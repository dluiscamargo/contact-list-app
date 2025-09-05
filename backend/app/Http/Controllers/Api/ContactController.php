<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Rules\ValidCpf;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contact::where('user_id', Auth::id());

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $sort_by = $request->input('sort_by', 'name');
        $sort_order = $request->input('sort_order', 'asc');
        $query->orderBy($sort_by, $sort_order);

        $per_page = $request->input('per_page', 15);
        $contacts = $query->paginate($per_page);

        return response()->json($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => [
                'required',
                'string',
                new ValidCpf,
                Rule::unique('contacts')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
            'phone' => 'required|string|max:20',
            'cep' => 'required|string|max:9',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
        ]);

        $address = "{$validatedData['street']}, {$validatedData['number']}, {$validatedData['city']}, {$validatedData['state']}";
        $lat = '-23.550520'; // Valor Padrão
        $lng = '-46.633308'; // Valor Padrão

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $address,
                    'format' => 'json'
                ],
                'headers' => [
                    'User-Agent' => 'ContactListApp/1.0'
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            if (!empty($data)) {
                $lat = $data[0]['lat'];
                $lng = $data[0]['lon'];
            }
        } catch (\Exception $e) {
            Log::error('Nominatim Geocoding API error: ' . $e->getMessage());
        }

        $contact = Contact::create(array_merge($validatedData, [
            'user_id' => Auth::id(),
            'latitude' => $lat,
            'longitude' => $lng,
        ]));

        return response()->json($contact, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        if ($contact->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        if ($contact->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'cpf' => [
                'sometimes',
                'required',
                'string',
                new ValidCpf,
                Rule::unique('contacts')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($contact->id)
            ],
            'phone' => 'sometimes|required|string|max:20',
            'cep' => 'sometimes|required|string|max:9',
            'street' => 'sometimes|required|string|max:255',
            'number' => 'sometimes|required|string|max:255',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'state' => 'sometimes|required|string|max:255',
        ]);

        $address = "{$validatedData['street']}, {$validatedData['number']}, {$validatedData['city']}, {$validatedData['state']}";
        $lat = $contact->latitude;
        $lng = $contact->longitude;

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $address,
                    'format' => 'json'
                ],
                'headers' => [
                    'User-Agent' => 'ContactListApp/1.0'
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            if (!empty($data)) {
                $lat = $data[0]['lat'];
                $lng = $data[0]['lon'];
            }
        } catch (\Exception $e) {
            Log::error('Nominatim Geocoding API error: ' . $e->getMessage());
        }

        $contact->update(array_merge($validatedData, [
            'latitude' => $lat,
            'longitude' => $lng,
        ]));

        return response()->json($contact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        if ($contact->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $contact->delete();

        return response()->json(null, 204);
    }
}

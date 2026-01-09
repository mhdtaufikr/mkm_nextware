<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\ApiEndpoint;

class ApiEndpointController extends Controller
{
    public function index()
    {
        $endpoints = ApiEndpoint::orderBy('id', 'desc')->get();
        return view('api_endpoints.index', compact('endpoints'));
    }

    // For edit modal (AJAX)
    public function show($id)
    {
        $endpoint = ApiEndpoint::findOrFail($id);

        return response()->json([
            'data' => $endpoint
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $endpoint = ApiEndpoint::create($data);

        return response()->json([
            'message' => 'Created',
            'data' => $endpoint
        ]);
    }

    public function update(Request $request, $id)
    {
        $endpoint = ApiEndpoint::findOrFail($id);

        $data = $this->validatePayload($request, $endpoint->id);

        $endpoint->update($data);

        return response()->json([
            'message' => 'Updated',
            'data' => $endpoint
        ]);
    }

    public function destroy($id)
    {
        $endpoint = ApiEndpoint::findOrFail($id);
        $endpoint->delete();

        return redirect()->back()->with('success', 'Deleted successfully');
    }

    private function validatePayload(Request $request, $id = null): array
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:255'],
            'code' => [
                'required','string','max:255',
                Rule::unique('api_endpoints', 'code')->ignore($id)
            ],
            'method' => ['required','string','max:10'],
            'base_url' => ['nullable','string','max:255'],
            'path' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],

            'auth_type' => ['nullable','string','max:30'],
            'auth_key' => ['nullable','string','max:255'],
            'auth_value' => ['nullable','string'],

            'is_active' => ['required'],

            // JSON text from textarea (we will parse safely below)
            'headers' => ['nullable','string'],
            'params' => ['nullable','string'],
            'body_template' => ['nullable','string'],
        ]);

        $validator->after(function($v) use ($request) {
            foreach (['headers','params','body_template'] as $field) {
                $val = $request->input($field);
                if ($val !== null && trim($val) !== '') {
                    json_decode($val, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $v->errors()->add($field, strtoupper($field) . ' must be valid JSON.');
                    }
                }
            }
        });

        $validator->validate();

        // Build final data (parse JSON fields)
        $data = $validator->validated();

        foreach (['headers','params','body_template'] as $field) {
            $val = $request->input($field);
            $data[$field] = (isset($val) && trim($val) !== '') ? json_decode($val, true) : null;
        }

        $data['is_active'] = (bool) $request->input('is_active');

        return $data;
    }
}

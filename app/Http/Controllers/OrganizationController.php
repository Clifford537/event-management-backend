<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationController extends Controller
{
    /**
     * @group Organizations
     * List all organizations
     * @authenticated
     * @response 200 [{"id":1,"name":"Test Org","slug":"test-org"}]
     */
    public function index()
    {
        // Ideally, only admins can see their own organizations or all orgs if super admin
        $organizations = Organization::all();

        return response()->json($organizations, 200);
    }

    /**
     * @group Organizations
     * Create a new organization
     * @authenticated
     * @bodyParam name string required The name of the organization
     * @response 201 {"organization":{"id":1,"name":"Test Org","slug":"test-org"}}
     * @response 422 {"message":"The name field is required.","errors":{"name":["The name field is required."]}}
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:organizations,name',
            ]);

            return DB::transaction(function () use ($data, $request) {
                $organization = Organization::create([
                    'name' => $data['name'],
                    'slug' => \Str::slug($data['name']),
                ]);

                // Associate user with organization (optional, depends on your design)
                $user = $request->user();
                $user->organization_id = $organization->id;
                $user->save();

                return response()->json(['organization' => $organization], 201);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Organization creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @group Organizations
     * Get an organization by slug
     * @urlParam slug string required The slug of the organization
     * @response 200 {"organization":{"id":1,"name":"Test Org","slug":"test-org"}}
     * @response 404 {"message":"Organization not found."}
     */
    public function show($slug)
    {
        $organization = Organization::where('slug', $slug)->first();

        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found.'
            ], 404);
        }

        return response()->json(['organization' => $organization], 200);
    }

    /**
     * @group Organizations
     * Update an organization
     * @authenticated
     * @urlParam slug string required The slug of the organization to update
     * @bodyParam name string required The new name of the organization
     * @response 200 {"organization":{"id":1,"name":"Updated Org","slug":"updated-org"}}
     * @response 422 {"message":"The name field is required.","errors":{"name":["The name field is required."]}}
     * @response 404 {"message":"Organization not found."}
     */
    public function update(Request $request, $slug)
    {
        $organization = Organization::where('slug', $slug)->first();

        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found.'
            ], 404);
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:organizations,name,' . $organization->id,
            ]);

            $organization->name = $data['name'];
            $organization->slug = \Str::slug($data['name']);
            $organization->save();

            return response()->json(['organization' => $organization], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Organization update failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @group Organizations
     * Soft delete an organization
     * @authenticated
     * @urlParam slug string required The slug of the organization to delete
     * @response 200 {"message":"Organization deleted successfully."}
     * @response 404 {"message":"Organization not found."}
     */
    public function destroy($slug)
    {
        $organization = Organization::where('slug', $slug)->first();

        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found.'
            ], 404);
        }

        try {
            $organization->delete(); // Soft delete requires `use SoftDeletes` on model

            return response()->json(['message' => 'Organization deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Organization deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

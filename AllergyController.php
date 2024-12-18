<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use Illuminate\Http\Request;

class AllergyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('allergy.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('allergy.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => ['required', 'string', 'unique:allergies,name'],
        ]);

        $Allergy = Allergy::create($data);

        return response()->json($Allergy, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Allergy $Allergy)
    {

        return response()->json($Allergy);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Allergy $allergy)
    {
        //
        return view('allergy.edit', [
            'allergy' => $allergy,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Allergy $allergy)
    {
        //

        $data = $request->validate([
            'name' => ['required', 'string', 'unique:categories,name,'.$allergy->id],
        ]);

        $allergy->update($data);

        return response()->json($allergy);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $delete = Allergy::findOrFail($id);
        $delete->delete();

        return response()->json(['message' => 'deleted successfully'], 200);

    }

    public function alldata(Request $request)
    {
        $allegies = Allergy::get();

        return response()->json($allegies);

        $transformedProducts = $allergies->map(function ($allergy) {
            return [
                'id' => $allergy->id,
                'name' => $allergy->name,
                'created_at' => $allergy->created_at,
                'updated_at' => $allergy->updated_at,
                'created_by' => $allergy->creator_user->name,
                'updated_by' => $allergy->updater_user->name,
            ];
        });

        return response()->json($transformedProducts);

    }
}

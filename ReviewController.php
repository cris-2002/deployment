<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // $reviews = Review::all();
        // return response()->json($reviews);

        return view('review.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $review = Review::create($request->all());

        return response()->json($review, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $review = Review::find($id);

        return response()->json($review);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $review = Review::find($id);
        $review->update($request->all());

        return response()->json($review);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        // Review::destroy($id);
        // return response()->json(null, 204);

        $delete = Review::findOrFail($id);
        $delete->delete();

        return response()->json(['message' => 'deleted successfully'], 200);

    }

    public function alldata(Request $request)
    {
        // $Review = Review::all();
        $Reviews = Review::with(['product', 'user'])->get();

        $transformedProducts = $Reviews->map(function ($Review) {
            return [
                'id' => $Review->id,
                'product_id' => $Review->product_id,
                'user_id' => $Review->user_id,
                'rating' => $Review->rating,
                'comment' => $Review->comment,
                'created_at' => $Review->created_at,
                'updated_at' => $Review->updated_at,
                'product_name' => $Review->product->name,
                'user_name' => $Review->user->name,
                'status' => $Review->status,
            ];
        });

        return response()->json($transformedProducts);
    }
}

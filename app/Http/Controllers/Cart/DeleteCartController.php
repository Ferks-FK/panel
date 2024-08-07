<?php

namespace App\Http\Controllers\Cart;

use App\Contracts\Eloquent\CartRepositoryInterface;
use Illuminate\Http\Request;

class DeleteCartController
{
    public function __construct(
        protected CartRepositoryInterface $cartRepositoryInterface)
    {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $id)
    {
        $this->cartRepositoryInterface->delete($id);

        return response()->json([
            'message' => 'Cart deleted successfully.'
        ]);
    }
}

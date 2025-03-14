<?php

namespace App\Http\Controllers\Product;

use App\Exceptions\Product\NotFoundProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ValidateProductStore;
use App\Http\Service\Image\SaveImage;
use App\Http\Service\PDF\SavePDF;
use App\Http\Service\PDF\StorePDF;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use SaveImage, SavePDF, StorePDF, ValidateProductStore;

    public function storeProduct(Request $request): JsonResponse
    {
        $this->validateProductStore($request);

        DB::transaction(function () use ($request) {
            $pdfId = $this->storePDF($request->pdf);


            $productId = Product::create([
                'name' => $request->name,
                'characteristics' => $request->characteristics,
                'benefits' => $request->benefits,
                'compatibility' => $request->compatibility,
                'price' => $request->price,
                'stock' => $request->stock,
                'pdf_id' => $pdfId,
            ])->id;

            $product = Product::find($productId);
            $product->subCategory()->attach($request->subcategory_id);

            $image = $this->saveImageBase64($request->image, 'products');
            $product->image()->create([
                'url' => $image
            ]);
        });


        return new JsonResponse(['data' => 'Producto registrado']);
    }

    public function updateProduct(string $nameProduct, Request $request)
    {

        $this->validateProductStore($request);
        $product = Product::where('name', $nameProduct)->first();

        if (!$product) {
            throw new NotFoundProduct();
        }

        $pathPdf = $this->savePDFBase64($request->pdf);
        $pdfId = $this->storePDF($pathPdf);
        $image = $this->saveImageBase64($request->image, 'products');

        $product->update([
            'name' => $request->name,
            'characteristics' => $request->characteristics,
            'benefits' => $request->benefits,
            'compatibility' => $request->compatibility,
            'price' => $request->price,
            'stock' => $request->stock,
            'pdf_id' => $pdfId,
        ]);
        $product->subCategory()->sync($request->subcategory_id);
        $product->image()->update([
            'url' => $image
        ]);

        return new JsonResponse(['data' => 'Registro actualizado']);
    }

    public function deleteProduct(string $nameProduct): JsonResponse
    {
        $product = Product::where('name', $nameProduct)->first();
        if (!$product) {
            throw new NotFoundProduct;
        }
        $product->delete();
        return new JsonResponse(['data' => 'Producto eliminado']);
    }

    public function test(Request $request)
    {
        $pdfPath = $this->savePDFBase64($request->pdf);
        //$path = $this->saveImageBase64($request->image);
        return new JsonResponse(['data' => $pdfPath]);
    }
}

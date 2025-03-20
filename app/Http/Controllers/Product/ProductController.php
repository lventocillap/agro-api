<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Service\PDF\StorePDF;
use App\Http\Service\PDF\UpdatePDF;
use App\Http\Service\PDF\DeletePDF;
use App\Http\Controllers\Controller;
use App\Http\Service\Image\SaveImage;
use App\Http\Service\Image\DeleteImage;
use App\Exceptions\Product\ProductExists;
use App\Exceptions\Product\NotFoundProduct;
use App\Http\utils\Product\FindProductExists;
use App\Http\Requests\Product\ValidateProductRequest;

class ProductController extends Controller
{
    use 
    StorePDF, 
    UpdatePDF, 
    DeletePDF,
    SaveImage, 
    DeleteImage, 
    FindProductExists, 
    ValidateProductRequest;

    public function storeProduct(Request $request): JsonResponse
    {
        $productExists = $this->findProductExists($request->name);
        if ($productExists) {
            throw new ProductExists;
        }
        $this->validateProducRequest($request);

        $benefits = implode('益', $request->benefits);

        DB::transaction(function () use ($request, $benefits) {
            $pdfId = $this->storePDF($request->pdf);
            $productId = Product::create([
                'name' => $request->name,
                'characteristics' => $request->characteristics,
                'benefits' => $benefits,
                'compatibility' => $request->compatibility,
                'price' => $request->price,
                'stock' => $request->stock,
                'pdf_id' => $pdfId,
            ])->id;

            $product = Product::find($productId);
            $product->subCategories()->attach($request->subcategory_id);

            $image = $this->saveImageBase64($request->image, 'products');
            $product->image()->create([
                'url' => $image
            ]);
        });
        return new JsonResponse(['data' => 'Producto registrado']);
    }

    public function updateProduct(string $nameProduct, Request $request)
    {
        $product = Product::where('name', $nameProduct)->first();
        if (!$product) {
            throw new NotFoundProduct();
        }
        $this->validateProducRequest($request);

        $benefits = implode('益', $request->benefits);
        
        $status = true;
        if ($request->stock === 0) {
            $status = false;
        }
        
        $this->deleteImage($product->image->url); 
        $this->deletePDF($product->pdf->url);
        
        $product->update([
            'name' => $request->name,
            'characteristics' => $request->characteristics,
            'benefits' => $benefits,
            'compatibility' => $request->compatibility,
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $status
        ]);
        
        $this->updatePDF($product, $request->pdf);
        $image = $this->saveImageBase64($request->image, 'products');
        
        $product->subCategories()->sync($request->subcategory_id);
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
        $this->deleteImage($product->image->url);
        $this->deletePDF($product->pdf->url);
        $product->delete();
        $product->image()->delete();
        $product->pdf()->delete();
        return new JsonResponse(['data' => 'Producto eliminado']);
    }

    public function getAllProducts(Request $request)
    {
        $nameProduct = $request->query('product');
        $subcategory = $request->query('category');
        $limit = $request->query('limit');

        $products = Product::select('id', 'name', 'price', 'status')
            ->with([
                'subCategories:id,name',
                'image:id,imageble_id,url',
            ])
            ->when($nameProduct, function ($query) use ($nameProduct) {
                $query->where('name', 'like', "%{$nameProduct}%");
            })->when($subcategory, function ($query) use ($subcategory) {
                $query->whereHas('subCategories', function ($subQuery) use ($subcategory) {
                    $subQuery->where('name', $subcategory);
                });
            })
            ->where('status', true)
            //->get(); 
            ->paginate($limit);
            
        return new JsonResponse([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'total' => $products->total(),
            'last_page' => $products->lastPage(),
            'next_page' => $products->nextPageUrl(),
            'prev_page' => $products->previousPageUrl()
        ]);
    }

    public function getProduct(string $nameProduct)
    {
        $product = Product::select(
            'id',
            'name',
            'characteristics',
            'benefits',
            'compatibility',
            'stock',
            'price',
            'status',
            'pdf_id'
        )
            ->with([
                'subcategories:id,name',
                'pdf:id,url',
                'image:id,imageble_id,url'
            ])
            ->where('name', $nameProduct)
            ->get()
            ->map(function (Product $item) {
                $benefits = explode('益', $item->benefits);
                $item->benefits = $benefits;
                return $item;
            });
        return new JsonResponse(['data' => $product]);
    }
}

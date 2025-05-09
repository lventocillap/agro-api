<?php

namespace App\Http\Controllers\Product;

use App\Exceptions\Image\NotFoundImage;
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
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

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

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Registrar un nuevo producto (el decuento es opcional)",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "characteristics", "benefits", "compatibility", "price", "stock", "pdf", "subcategory_id", "image"},
     *             @OA\Property(property="name", type="string", example="Fertilizante Orgánico"),
     *             @OA\Property(property="characteristics", type="string", example="Mejora la calidad del suelo"),
     *             @OA\Property(
     *                 property="benefits",
     *                 type="array",
     *                 @OA\Items(type="string", example="Aumenta la producción")
     *             ),
     *             @OA\Property(property="compatibility", type="string", example="Comvatible con cultivos de frutas"),
     *             @OA\Property(property="price", type="number", format="float", example=49.99),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="discount", type="integer", example=50),
     *             @OA\Property(property="subcategory_id", type="array", @OA\Items(type="integer"), example={1,2}),
     *             @OA\Property(property="image", type="string", format="binary", description="Imagen en formato base64")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Producto registrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="El producto ya existe",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El producto ya existe en la base de datos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo name es requerido")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function storeProduct(Request $request): JsonResponse
    {
        $productExists = $this->findProductExists($request->name);
        if ($productExists) {
            throw new ProductExists;
        }
        $this->validateProducRequest($request);

        $benefits = implode('益', $request->benefits);

        DB::transaction(function () use ($request, $benefits) {
            $pdfId = $this->storePDF(null);
            $productId = Product::create([
                'name' => $request->name,
                'characteristics' => $request->characteristics,
                'benefits' => $benefits,
                'compatibility' => $request->compatibility,
                'price' => $request->price,
                'stock' => $request->stock,
                'pdf_id' => $pdfId,
                'discount' => $request->discount
            ])->id;

            $product = Product::find($productId);
            $product->subCategories()->attach($request->subcategory_id);

            // $image = $this->saveImageBase64($request->image, 'products');
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    
                    $product->image()->create([
                        'url' => asset(Storage::url($path))
                    ]);
                }
            }

            // $product->image()->create([
            //     'url' => $image
            // ]);
        });
        return new JsonResponse(['data' => 'Producto registrado']);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{nameProduct}",
     *     summary="Actualizar un producto (el descuento es opcional)",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="nameProduct",
     *         in="path",
     *         required=true,
     *         description="Nombre del producto a actualizar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "characteristics", "benefits", "compatibility", "price", "stock", "pdf", "subcategory_id", "image"},
     *             @OA\Property(property="name", type="string", example="Fertilizante Orgánico"),
     *             @OA\Property(property="characteristics", type="string", example="Mejora la calidad del suelo"),
     *             @OA\Property(
     *                 property="benefits",
     *                 type="array",
     *                 @OA\Items(type="string", example="Aumenta la producción")
     *             ),
     *             @OA\Property(property="compatibility", type="string", example="Compatible con cultivos de frutas"),
     *             @OA\Property(property="price", type="number", format="float", example=49.99),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="discount", type="integer", example=50),
     *             @OA\Property(property="subcategory_id", type="array", @OA\Items(type="integer"), example={1,2}),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Registro actualizado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo name es requerido")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function updateProduct(string $nameProduct, Request $request)
    {
        $product = Product::where('name', $nameProduct)->first();
        if (!$product) {
            throw new NotFoundProduct();
        }
        $sameName = $nameProduct !== $request->name;
        if ($sameName) {
            $this->validateProducRequest($request);
        }
        $benefits = implode('益', $request->benefits);

        $status = true;
        if ($request->stock === 0) {
            $status = false;
        }
        $product->update([
            'name' => $request->name,
            'characteristics' => $request->characteristics,
            'benefits' => $benefits,
            'compatibility' => $request->compatibility,
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $status,
            'discount' => $request->discount
        ]);

        if ($request->filled('delete_images')) {
            $idsToDelete = $request-> delete_images;
            $product->image()
                 ->whereIn('id', $idsToDelete)
                 ->get()
                 ->each(function($img){
                     $this->deleteImageForUrl($img -> url);
                     $img->delete();
                 });
        }

        if ($request->hasFile('images')) {
            // $product->image()->delete();
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                
                $product->image()->create([
                    'url' => Storage::url($path)
                ]);
            }
        }
        
        $product->subCategories()->sync($request->subcategory_id);

        return new JsonResponse(['data' => 'Registro actualizado']);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{nameProduct}",
     *     summary="Eliminar un producto",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="nameProduct",
     *         in="path",
     *         required=true,
     *         description="Nombre del producto a eliminar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Producto eliminado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     )
     * )
     */
    public function deleteProduct(string $nameProduct): JsonResponse
    {
        $product = Product::with('image')
                      ->where('name', $nameProduct)
                      ->firstOrFail();

        // Borrar imágenes en disco y BD
        foreach ($product->image as $img) {
            $this->deleteImageForUrl($img->url);
            $img->delete();
        }
        // Borrar producto
        $product->delete();

        // Borrar PDF
        if ($product->pdf) {
            $this->deletePDF($product->pdf->url);
            $product->pdf()->delete();
        }


        return response()->json(['data' => 'Producto eliminado']);
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Obtener lista de productos",
     *     description="Devuelve una lista paginada de productos, con opción de filtrado por nombre y subcategoría. 
     *     Si el usuario está autenticado mediante un token en el encabezado Authorization, verá todos los productos. 
     *     Si no está autenticado, solo se devolverán los productos activos.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="query",
     *         required=false,
     *         description="Buscar productos por nombre (coincidencia parcial).",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="subcategory",
     *         in="query",
     *         required=false,
     *         description="Filtrar productos por subcategoría.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Cantidad máxima de productos por página (por defecto 10).",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filtrar productos por categoria.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="discount",
     *         in="query",
     *         required=false,
     *         description="Filtrar si tiene promociones.",
     *         @OA\Schema(type="boolean", default=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos paginada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tomate"),
     *                 @OA\Property(property="price", type="number", format="float", example=2.50),
     *                 @OA\Property(property="stock", type="integer", example=10),
     *                 @OA\Property(property="discount", type="integer", example=50),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="created", type="string", format="date-time", example="2025-04-24 12:42:31"),
     *                 @OA\Property(property="categories", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Alimentos"),
     *                     @OA\Property(property="sub_categories", type="array", @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Verduras")
     *                     ))
     *                 )),
     *                 @OA\Property(property="images", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="url", type="string", example="https://example.com/image.jpg")
     *                 ))
     *             )),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total", type="integer", example=100),
     *             @OA\Property(property="last_page", type="integer", example=10),
     *             @OA\Property(property="next_page", type="string", nullable=true, example="http://api.example.com/products?page=2"),
     *             @OA\Property(property="prev_page", type="string", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function getAllProducts(Request $request)
    {
        $nameProduct = $request->query('product');
        $subcategory = $request->query('subcategory');
        $category = $request->query('category');
        $limit = $request->query('limit');
        $discount = $request->query('discount');
        $user = auth('api')->user();
        $products = Product::select('id', 'name', 'price', 'stock', 'status', 'discount', 'created_at')
            ->with([
                'subCategories.category:id,name',
                'image:id,imageble_id,url',
            ])
            ->when($nameProduct, function ($query) use ($nameProduct) {
                $query->where('name', 'like', "%{$nameProduct}%");
            })
            ->when($subcategory, function ($query) use ($subcategory) {
                $query->whereHas('subCategories', function ($subQuery) use ($subcategory) {
                    $subQuery->where('name', $subcategory);
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->whereHas('subCategories.category', function ($query) use ($category) {
                    $query->where('name', $category);
                });
            })
            ->when(is_null($user), function ($query) {
                $query->where('status', true);
            })
            ->when($discount, function ($query) {
                $query->where('discount', '>', 0);
            })
            ->orderByDesc('id')
            ->paginate($limit);

        $products->getCollection()->transform(function (Product $product) {
            $grouped = [];

            foreach ($product->subCategories as $sub) {
                $category = $sub->category;
                $catId = $category->id;

                if (!isset($grouped[$catId])) {
                    $grouped[$catId] = [
                        'id' => $catId,
                        'name' => $category->name,
                        'sub_categories' => [],
                    ];
                }

                $grouped[$catId]['sub_categories'][] = [
                    'id' => $sub->id,
                    'name' => $sub->name,
                ];
            }

            $product->setAttribute('created', $product->created_at->format('Y-m-d H:i:s'));

            // Agregamos la propiedad virtual 'categories'
            $product->setAttribute('categories', array_values($grouped));
            // Opcional: eliminar la lista plana de sub_categories
            $product->unsetRelation('subCategories');

            return $product;
        });

        return new JsonResponse([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'total' => $products->total(),
            'last_page' => $products->lastPage(),
            'next_page' => $products->nextPageUrl(),
            'prev_page' => $products->previousPageUrl()
        ]);
    }
    /**
     * @OA\Get(
     *     path="/api/products/{nameProduct}",
     *     summary="Obtener un producto por su nombre",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="nameProduct",
     *         in="path",
     *         required=true,
     *         description="Nombre del producto a consultar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del producto",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Acidas"),
     *                 @OA\Property(property="characteristics", type="string", example="AcidPulse pH es un regulador de pH diseñado..."),
     *                 @OA\Property(property="benefits", type="array", @OA\Items(type="string", example="Mejora la Absorción de Nutrientes")),
     *                 @OA\Property(property="compatibility", type="string", example="Compatible con la mayoría de los fertilizantes..."),
     *                 @OA\Property(property="stock", type="integer", example=2),
     *                 @OA\Property(property="price", type="integer", example=10),
     *                 @OA\Property(property="discount", type="integer", example=50),
     *                 @OA\Property(property="status", type="integer", example=1),
     *                 @OA\Property(property="created", type="string", format="date-time", example="2025-04-24 12:42:31"),
     *                 @OA\Property(property="categories", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Especialidades"),
     *                     @OA\Property(property="sub_categories", type="array", @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Coadyuvante")
     *                     ))
     *                 )),
     *                 @OA\Property(property="pdf", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="url", type="string", example="http://127.0.0.1:8000/storage/products/fc0722ef-9a9a-40b1-b286-d5006145144e.pdf")
     *                 ),
     *                 @OA\Property(property="images", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="url", type="string", example="http://127.0.0.1:8000/storage/products/fc0722ef-9a9a-40b1-b286-d5006145144e.png")
     *                 ))
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Producto no encontrado")
     *         )
     *     )
     * )
     */
    public function getProduct(string $nameProduct): JsonResponse
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
            'discount',
            'pdf_id',
            'created_at',
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

                $item->setAttribute('created', $item->created_at->format('Y-m-d H:i:s'));

                return $item;
            });
        return new JsonResponse(['data' => $product]);
    }

    /**
     * @OA\Post(
     *     path="/products/{productId}/image",
     *     summary="Subir imagen de un producto en Base64",
     *     description="Guarda una imagen codificada en Base64 para un producto específico.",
     *     operationId="storeImageProduct",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Imagen codificada en Base64",
     *         @OA\JsonContent(
     *             required={"image"},
     *             @OA\Property(
     *                 property="image",
     *                 type="string",
     *                 format="byte",
     *                 example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA..."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Imagen guardada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tomate"),
     *                 @OA\Property(property="description", type="string", example="Producto agrícola fresco"),
     *                 @OA\Property(property="price", type="number", format="float", example=3.50),
     *                 @OA\Property(
     *                     property="image",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=10),
     *                         @OA\Property(property="url", type="string", example="/uploads/products/tomate.jpg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     )
     * )
     */

    public function storeImageProdcut(Request $request, int $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new NotFoundProduct;
        }
        $image = $this->saveImage($request->image, 'products');
        $product->image()->create([
            'url' => $image
        ]);
        return new JsonResponse([
            'data' => $product->load('image')
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/products/image/{imageId}",
     *     summary="Eliminar imagen de un producto",
     *     description="Elimina una imagen del producto por su ID y borra el archivo del servidor.",
     *     operationId="deleteImageProduct",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="imageId",
     *         in="path",
     *         description="ID de la imagen a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Imagen eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="string",
     *                 example="Se elimino la imagen"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Imagen no encontrada"
     *     )
     * )
     */

    public function deleteImageProduct(int $imageId): JsonResponse
    {
        $image = Image::find($imageId);
        if (!$image) {
            throw new NotFoundImage;
        }
        $this->deleteImage($image->url);
        $image->delete();
        return new JsonResponse([
            'data' => 'Se elimino la imagen'
        ]);
    }
}

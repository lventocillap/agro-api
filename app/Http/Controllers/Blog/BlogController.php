<?php

namespace App\Http\Controllers\Blog;

use App\Exceptions\Blog\NotFoundBlog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\ValidateBlogRequest;
use App\Http\Service\Image\DeleteImage;
use App\Http\Service\Image\SaveImage;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    use SaveImage, ValidateBlogRequest, DeleteImage;
    public function storeBlog(Request $request): JsonResponse
    {

        $this->validateBlogRequest($request);

        $blog = Blog::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id
        ]);

        $image = $this->saveImageBase64($request->image, 'blogs');
        $blog->image()->create([
            'url' => $image
        ]);
        return new JsonResponse(['data' => 'Blog registrado']);
    }

    public function updateBlog(Request $request, int $idBlog): JsonResponse
    {
        $this->validateBlogRequest($request);
        $blog = Blog::find($idBlog);
        if (!$blog) {
            throw new NotFoundBlog;
        }
        $blog->update([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id
        ]);
        $this->deleteImage($blog->image->url);
        $image = $this->saveImageBase64($request->image, 'blogs');
        $blog->image()->update([
            'url' => $image
        ]);
        return new JsonResponse(['data' => 'Blog actulizado']);
    }

    public function deleteBlog(int $idBlog): JsonResponse
    {
        $blog = Blog::find($idBlog);
        if(!$blog){
            throw new NotFoundBlog;
        }
        $this->deleteImage($blog->image->url);
        $blog->delete();
        return new JsonResponse(['data' => 'Blog eliminado']);
    }

    public function getAllBlogs(Request $request): JsonResponse
    {
        $limit = $request->query('limit');
        $category = $request->query('category');
        $blogs = Blog::select('id', 'title', 'description', 'category_id')
        ->with([
            'category:id,name',
            'image:url,imageble_id'
        ])
        ->when($category, function ($query) use($category){
            $query->whereHas('category', function($subquery) use($category){
                $subquery->where('name', $category);
            });
        })
        ->paginate($limit);
        return new JsonResponse([
            'data' => $blogs->items(),
            'current_page' => $blogs->currentPage(),
            'total' => $blogs->total(),
            'last_page' => $blogs->lastPage(),
            'next_page' => $blogs->nextPageUrl(),
            'prev_page' => $blogs->previousPageUrl()
        ]);;
    }
}

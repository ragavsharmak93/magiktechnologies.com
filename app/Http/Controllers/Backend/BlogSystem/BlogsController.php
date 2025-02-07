<?php

namespace App\Http\Controllers\Backend\BlogSystem;

use App\Models\Tag;
use App\Models\Blog;
use App\Models\BlogTag;
use App\Models\Language;
use App\Services\WriteBotService;
use Illuminate\Support\Str;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Models\BlogLocalization;
use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequestForm;

class BlogsController extends Controller
{
    # construct
    public function __construct()
    {
        $this->middleware(['permission:blogs'])->only('index');
        $this->middleware(['permission:add_blogs'])->only(['create', 'store']);
        $this->middleware(['permission:edit_blogs'])->only(['edit', 'update']);
        $this->middleware(['permission:publish_blogs'])->only(['updateStatus']);
        $this->middleware(['permission:delete_blogs'])->only(['delete']);
    }

    # blog list
    public function index(Request $request, WriteBotService $writeBotService)
    {
        $searchKey = null;
        $is_published = null;

        $blogs = $writeBotService->getAllBlogs(false, true);

       // $blogs = Blog::oldest();
        if ($request->search != null) {
            $searchKey = $request->search;
        }

        if ($request->is_published != null) {
            $is_published    = $request->is_published;
        }

        return view('backend.pages.blogSystem.blogs.index', compact('blogs', 'searchKey', 'is_published'));
    }


    # return view of create form
    public function create()
    {
        $categories = BlogCategory::all();
        $tags = Tag::all();
        return view('backend.pages.blogSystem.blogs.create', compact('categories', 'tags'));
    }

    # blog store
    public function store(BlogRequestForm $request)
    {
        $exitSlugID = Blog::where('slug', $request->slug)->value('id');
        $blog = new Blog;
        $blog->title = $request->title;
        $blog->thumbnail_image = $request->image;
        $blog->banner = $request->banner;
        $blog->meta_img = $request->meta_image;
        $blog->slug = convertToSlug($request->title) .$exitSlugID;
        $blog->blog_category_id = $request->category_id;
        $blog->short_description = $request->short_description;

        $blog->video_link = $request->video_link;
        $blog->description = $request->description;

        $blog->meta_title = $request->meta_title;
        $blog->meta_description = $request->meta_description;

        $blog->save();
        $blog->tags()->sync($request->tag_ids);

        $blogLocalization = BlogLocalization::firstOrNew(['lang_key' => env('DEFAULT_LANGUAGE'), 'blog_id' => $blog->id]);
        $blogLocalization->title = $blog->title;
        $blogLocalization->short_description = $blog->short_description;
        $blogLocalization->description = $blog->description;
        $blogLocalization->save();

        $blog->save();
        flash(localize('Blog has been inserted successfully'))->success();
        return redirect()->route('admin.blogs.index');
    }

    # edit blog
    public function edit(Request $request, $id)
    {
        $lang_key = $request->lang_key;
        $language = Language::isActive()->where('code', $lang_key)->first();
        if (!$language) {
            flash(localize('Language you are trying to translate is not available or not active'))->error();
            return redirect()->route('admin.blogs.index');
        }

        $categories = BlogCategory::all();
        $tags = Tag::all();

        $blog = Blog::findOrFail($id);
        return view('backend.pages.blogSystem.blogs.edit', compact('blog', 'categories', 'tags', 'lang_key'));
    }

    # update blog
    public function update(BlogRequestForm $request)
    {
        $blog = Blog::findOrFail($request->id);
        $exitSlugId = Blog::where('slug', $request->slug)->where('id', '!=', $request->id)->value('id');

        if ($request->lang_key == env("DEFAULT_LANGUAGE")) {

            $blog->title = $request->title;
            $blog->slug = $request->slug != null ? $request->slug.$exitSlugId : convertToSlug($request->title) .$exitSlugId;
            $blog->blog_category_id = $request->category_id;

            $blog->thumbnail_image = $request->image;
            $blog->banner = $request->banner;
            $blog->meta_img = $request->meta_image;

            $blog->short_description = $request->short_description;
            $blog->description = $request->description;

            $blog->video_link = $request->video_link;


            $blog->meta_title = $request->meta_title;
            $blog->meta_description = $request->meta_description;

            $blog->save();
            $blog->tags()->sync($request->tag_ids);
        }

        $blogLocalization = BlogLocalization::firstOrNew(['lang_key' => $request->lang_key, 'blog_id' => $blog->id]);
        $blogLocalization->title = $request->title;
        $blogLocalization->short_description = $request->short_description;
        $blogLocalization->description = $request->description;

        $blog->save();
        $blogLocalization->save();

        flash(localize('Blog has been updated successfully'))->success();
        return redirect()->route('admin.blogs.index');
    }

    # update popular
    public function updatePopular(Request $request)
    {
        $blog = Blog::findOrFail($request->id);
        $blog->is_popular = $request->is_popular;
        if ($blog->save()) {
            return 1;
        }
        return 0;
    }

    # update status
    public function updateStatus(Request $request)
    {
        $blog = Blog::findOrFail($request->id);
        $blog->is_active = $request->is_active;
        if ($blog->save()) {
            return 1;
        }
        return 0;
    }

    # delete blog
    public function delete($id)
    {
        $blog = Blog::findOrFail($id);
        BlogTag::where('blog_id', $blog->id)->delete();
        $blog->delete();
        flash(localize('Blog has been deleted successfully'))->success();
        return back();
    }
}

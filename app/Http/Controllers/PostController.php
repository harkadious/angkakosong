<?php

namespace App\Http\Controllers;
use App\Posts;
use App\User;
use Redirect;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostFormRequest;
use Illuminate\Http\Request;

class PostController extends Controller
{
	//
	public function index(){
		//fetch 5 posts from database which are active and latest
		$posts = Posts::where('active',1)->orderBy('created_at','desc')->paginate(5);
		//page heading
		$title = 'Latest Posts';
		//return home.blade.php template from resources/views folder
		return view('home')->withPosts($posts)->withTitle($title);
	}

	public function create(Request $request){
		// if user can post i.e. user is su or writer
		if($request->user()->can_post()){
			return view('posts.create');
		}	
		else{
			return redirect('/')->withErrors('You do not have sufficient permissions for writing post');
		}
	}

	public function store(PostFormRequest $request){
		$post = new Posts();
		$post->title = $request->get('title');
		$post->body = $request->get('body');
		$post->slug = str_slug($post->title);
		$post->writer_id = $request->user()->id;
		if($request->has('save')){
			$post->active = 0;
			$message = 'Post saved successfully';
		}			
		else{
			$post->active = 1;
			$message = 'Post published successfully';
		}
		$post->save();
		
		return redirect('edit/post/' . $post->slug)->withMessage($message);
	}

	public function show($slug){
		$post = Posts::where('slug', $slug)->first();
		if(!$post){
			return redirect('/')->withErrors('Requested page not found!');
		}
		$comments = $post->comments;
		return view('posts.show')->withPost($post)->withComments($comments);
	}

	public function edit(Request $req, $slug){
		$post = Posts::where('slug', $slug)->first();
		if($post && ($req->user()->id == $post->writer_id || $req->user()->is_superuser())){
			return view('posts.edit')->with('post',$post);
		}
		return redirect('/')->withErrors('You do not have sufficient permissions!');
	}

	public function update(Request $req){
		$post_id = $req->input('post_id');
		$post = Posts::find($post_id);
		if($post && ($post->writer_id == $req->user()->id || $req->user()->is_superuser())){
			$title = $req->input('title');
			$slug = str_slug($title);
			$duplicate = Posts::where('slug', $slug)->first();
			if($duplicate){
				if($duplicate->id != $post_id){
					return redirect('edit/' . $post->slug)->withErrors('Title already exists!')->withInput();
				}
				else{
					$post->slug = $slug;
				}
			}
			$post->title = $title;
			$post->body = $req->input('body');
			if($req->has('save')){
				$post->active = 0;
				$message = 'Post saved successfully';
				$landing = 'edit/'.$post->slug;
			}
			else{
				$post->active = 1;
				$message = 'Post updated successfully';
				$landing = $post->slug;
			}
			$post->save();
			return redirect($landing)->withMessage($message);
		}
		else{
			return redirect('/')->withErrors('You do not have sufficient permissions!');
		}
	}

	public function delete(Request $req, $id){
		$post = Posts::find($id);
		if($post && ($post->writer_id == $req->user()->id || $req->user()->is_superuser())){
			$post->delete();
			$data['message'] = 'Post deleted Successfully';
		}
		else{
			$data['errors'] = 'Invalid Operation. You do not have sufficient permissions!';
		}
		
		return redirect('/')->with($data);
	}

}

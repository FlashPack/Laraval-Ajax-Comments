<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comment;
use App\CommentRating;
use DB;
class CommentsController extends Controller
{
    //
    public function __constrct(){
        $this->middlewear('auth',['except'=>'get_comments']);
    }
    public function get_comments(Request $request){
        $module = $request->input('module');
        $module_id = $request->input('module_id');
        $setup=$request->input('setup');
        $limit = $request->input('displayLimit');
        $offset = $request->input('offset');
        $allow_rating = $request->input('allow_rating');
        $comments = Comment::when($allow_rating=='true' && auth()->id(),function($query){
                        return $query->select('comments.*',
                            DB::raw("(select up_vote from comment_ratings as cr where cr.comment_id=comments.id && cr.user_id='".auth()->id()."' limit 1) as user_up_vote"),
                            DB::raw("(select down_vote from comment_ratings as cr where cr.comment_id=comments.id && cr.user_id='".auth()->id()."' limit 1) as user_down_vote")
                        );
                    }) 
                        ->where('module',$module)->where('module_id',$module_id)->where('parent_id',0)
                        ->offset($offset)->limit($limit)->get();

        $all_comments_count = Comment::where('module',$module)->where('module_id',$module_id)->where('parent_id',0)->count();
        
        if($setup=='true'){
            return view('ajax-comments/index',compact('comments','setup','all_comments_count','allow_rating'));
        }else{
            return view('ajax-comments/comments',compact('comments','allow_rating'));
        }
    }
    public function new_comment(Request $request){
        $module = $request->input('module');
        $module_id = $request->input('module_id');
        $is_reply = $request->input('is_reply');
        $comment_content = $request->input('comment_content');
        $allow_rating = $request->input('allow_rating');
        if( !empty($module ) && !empty($module_id) && !empty($comment_content)  ){
            $this->validate($request,[
                'comment_content'=>'required|string|min:2',
            ]);
            $comment = new Comment(); 
            $comment->module = $module;
            $comment->module_id = $module_id;
            if($request->has('parent_id'))
                $comment->parent_id =$request->input('parent_id');
            $comment->content = $comment_content;
            $comment->user_id=auth()->id();
            $comment->up_votes = 1;
            $comment->save();
            
            if($allow_rating=='true'){
                $comment_rating = new CommentRating(); 
                $comment_rating->comment_id  = $comment->id; 
                $comment_rating->user_id = auth()->id();
                $comment_rating->up_vote = 1; 
                $comment_rating->save();

                $comment->user_up_vote = 1;
               
            }

            if($is_reply=='true')
                return view('ajax-comments/reply', [ 'reply'=>$comment ]);
            else
                return view('ajax-comments/comments', [ 'comments'=>[$comment],'allow_rating'=>$allow_rating ]);
        }
        return json_encode(['status'=>'failed']);
    }
    public function save_comment(Request $request){
        $comment_id = $request->input('comment_id');
        $comment_content = $request->input('comment_content');
        $comment = Comment::find($comment_id); 
        if(auth()->id()==$comment->user_id){
            $comment->content = $comment_content;
            $comment->save();
            return json_encode(['status'=>'success']);
        }
        return json_encode(['status'=>'failed']);
    }
    public function delete_comment(Request $request){
       
        $this->validate($request,[
            'comment_id'=>'required|integer',
        ]);
        $comment = Comment::find($request->input('comment_id')); 
        if($comment->user_id != auth()->id()){
            abort(401);
        }
        $comment->delete_children();
        $comment->delete();
        return json_encode(['status'=>'success']);
    }
    public function reply_form(Request $request){
        return view('ajax-comments/reply-form');
    }
    public function vote_comment(Request $request){
        $comment_id = $request->input('comment_id');
        $vote = $request->input('vote'); 
        $user_rating = CommentRating::where('comment_id',$comment_id)->where('user_id',auth()->id())->first();
        if($user_rating){
            $rating = CommentRating::find($user_rating->id);
        }else{
            $rating = new CommentRating();
            $rating->comment_id = $comment_id; 
            $rating->user_id = auth()->id();
        }

        if($vote=='up_vote'){
            $rating->up_vote=1;
            $rating->down_vote=0;    
        }elseif('down_vote'){
            $rating->down_vote=1;
            $rating->up_vote=0;
        }
        $rating->save();
        
        $comment = Comment::find($comment_id); 
        $comment->update_votes();
    }
}
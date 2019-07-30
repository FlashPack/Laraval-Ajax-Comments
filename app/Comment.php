<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CommentRating;
use DB;
class Comment extends Model
{
    //
    function user(){
        return $this->belongsTo('App\User');
    }
    function replies(){
        return $this->hasMany('App\Comment','parent_id');
    }
    function delete_children($parent_id=null){
        if($parent_id==null){
            $parent_id=$this->id;
        }
        $children = $this->where('parent_id',$parent_id)->get();
       if(count($children)==0){
           return;
       }else{
           foreach($children as $child){
               $child->delete();
           }
       }
    }
    function delete(){
        $this->where('id',$this->id)->delete(); 
        
        DB::table('comment_ratings')->where('comment_id',$this->id)->delete();
    }
    function update_votes(){
        $votes_count = DB::table('comment_ratings')->selectRaw('sum(up_vote) as up_votes,sum(down_vote) as down_votes')->where('comment_id',$this->id)->first();
        $this->up_votes   = $votes_count->up_votes;
        $this->down_votes = $votes_count->down_votes;
        $this->save();
    }
    
}

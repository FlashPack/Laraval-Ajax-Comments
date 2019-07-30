			
@foreach($comments as $comment)
	<li class="card mb-3 col-md-12 @if($comment->id==0) green-border @endif" id="comment_{{$comment->id}}" >
		<div class="container p-2">
			<div class="row">
				<div class="col-md-11">
					<div class="media mt-2">
						@if($allow_rating=='true')
						<div class="comment-rating">
							<button  data-comment-id="{{$comment->id}}"
								@auth 
									@if($comment->user_up_vote===1)
										class="up-vote-btn active" disabled="true"  
									@else
										class="up-vote-btn"
									@endif 									
								@else
									class="up-vote-btn" disabled="true" title="قم بتسجيل الدخول لتتمكن من تقييم التعليقات"  
								@endauth
								
							></button>
							<span class="rating-count">{{$comment->up_votes-$comment->down_votes}}</span>
							<button data-comment-id="{{$comment->id}}" 
							@auth 
									@if($comment->user_down_vote===1)
										class="down-vote-btn active" disabled="true"  
									@else
										class="down-vote-btn"
									@endif 									
								@else
									class="down-vote-btn" disabled="true" title="قم بتسجيل الدخول لتتمكن من تقييم التعليقات"  
								@endauth
								
							></button>
						</div>
						@endif
						<a href="/user/{{$comment->user_id}}"><img src="@if ($comment->user->profile_pic)/storage/{{$comment->user->profile_pic}} @else {{asset('images/avatar.jpg')}} @endif" class="border rounded mr-2" width="64" height="64" /></a>
						<div class="col-md-12 mr-2">
							<div class="user_name mb-3" style="text-align:right">
								<a href="/user/{{$comment->user_id}}">
								<small class="text-muted pull-left created-at"> {{$comment->created_at}} </small>
								{{$comment->user->first_name}} {{$comment->user->last_name}}
								</a>
								
							</div>
							
							
							<p style="clear:both" class="comment-content" id="comment_co_{{$comment->id}}">
							{{$comment->content}}
							</p>
							<ul class="list-inline size-11">
							@if(auth()->id()==$comment->user->id) 
								
								<li class="pull-left">
									<a href="javascript:;" class="text-danger comment-delete" data-comment-id="{{$comment->id}}"><i class="fas fa-trash-alt">حذف</i></a>
								</li>
								<li class="pull-left">
									<a href="javascript:;" class="text-dark comment-edit" data-comment-id="{{$comment->id}}"><i class="fas fa-edit">تعديل</i></a>
								</li>				
							@endif
							</ul>
						</div>
						
					</div>						
				</div>
			</div>
			<!-- Comments -->
			<ul class="replies_list list-unstyled">
				@foreach($comment->replies as $reply)
					@include('ajax-comments.reply')
				@endforeach
			</ul>
			<!-- End Comments -->
			@auth
			<button type="submit" class="btn btn-sm btn-primary pull-left" name="reply-add" data-parent-id="{{$comment->id}}" >إضافة رد</button>
			@endauth
		</div>
	</li>
@endforeach

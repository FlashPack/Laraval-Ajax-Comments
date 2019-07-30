	<li class="comment comment-reply" id="comment_{{$reply->id}}">
		
			<!-- avatar -->
			<img class="avatar" src="@if ($reply->user->profile_pic)/storage/{{$reply->user->profile_pic}} @else {{asset('images/avatar.jpg')}} @endif" width="35" height="35" alt="avatar">

			<!-- comment body -->
			<div class="comment-body"> 
				<a href="#" class="comment-author">
					<small class="text-muted pull-left created-at"> {{$reply->created_at}} </small>
					<span>{{$reply->user->first_name}} {{$reply->user->last_name}}</span>
				</a>
				<p class="comment-content">
					{!!$reply->content!!}   
				</p>
			</div><!-- /comment body -->

			<!-- options -->
			<ul class="list-inline size-11">
				@if( auth()->id() == $reply->user->id )
					<li class="pull-left">
						<a href="javascript:;" class="text-danger comment-delete" data-comment-id="{{$reply->id}}"><i class="fas fa-trash-alt"></i></a>
					</li>
					<li class="pull-left">
						<a href="javascript:;" class="text-dark comment-edit" data-comment-id="{{$reply->id}}"><i class="fas fa-edit"></i></a>
					</li>				
				@endif
			</ul><!-- /options -->
	</li>
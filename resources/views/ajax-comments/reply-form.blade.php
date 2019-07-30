<div id="reply_form">
		<form method="post" data-parent-id="" >
			{{ csrf_field() }}
			<li class="comment comment-reply">
				<!-- avatar -->
				<img class="avatar" src="@if (Auth::user()->profile_pic)/storage/{{Auth::user()->profile_pic}} @else {{asset('images/avatar.jpg')}} @endif" width="35" height="35" alt="avatar">

				<!-- comment body -->
				<div class="comment-body">
					<a href="#" class="comment-author">
						<span>{{ Auth::user()->first_name }}  {{ Auth::user()->last_name }} </span>
					</a>
					<div class="comment-form">
						<textarea class="form-control mb-10" name="reply-content"></textarea>
						<button type="button" class="btn btn-sm btn-primary pull-left mt-1 reply-submit" name="reply-submit" data-parent-id=""  disabled="true" >نشر </button>
						<br clear="all" />
					</div>
				</div><!-- /comment body -->
			</li>
		</form>
	</div>
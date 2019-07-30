@if($setup=='true')
<div class="well text-center">
		@auth()
		<form  method="post">
			{{csrf_field()}}
			<textarea rows="2" class="form-control" placeholder="What's on your mind?" name="comment-content"></textarea>
				<button type="submit" name="comment-submit" class="btn btn-sm btn-primary pull-left mt-1" disabled="true">نشر</button>
				<div id="error" class="text-danger" ></div>
				<div class=" mb-4"> 
			</div>
		</form>
		@else

			<!--قم <a href="/register?ref=">إنشاء حساب</a> أو <a href="javascript:;" onclick="window.open('/login?popup=true&ref={{urlencode(url()->current())}}','popUpWindow','height=500,width=500,left=600,top=100,left=100,resizable=no,scrollbars=no,toolbar=yes,menubar=no,location=no,directories=no, status=yes');">تسجيل الدخول</a>  لتتمكن من المشاركة-->
			قم <a href="/register?ref=">إنشاء حساب</a> أو <a href="javascript:;" onclick="openPopUp('/login?popup=true&ref={{urlencode(url()->current())}}',550,600)">تسجيل الدخول</a>  لتتمكن من المشاركة

		@endauth
</div>
@endif
<ul id="comment-list" class="list-unstyled">
	@include('ajax-comments/comments')
</ul>
@if($setup=='true' && count($comments)<$all_comments_count )

<div class="text-center">
<button type="button" class="btn btn-default" id="load-more-comments" data-comments-count="{{$all_comments_count}}" >  المزيد من التعليقات </button>
</div>

@endif
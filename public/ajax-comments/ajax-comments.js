(function($){
	$.fn.ajaxComments = function(options){
		let defaultSettings={
			module:null,
			module_id:null,
			_token:null,
			displayLimit:10,
			displayOffset:0,
			allowRating:true,
			minChar:2,
			loadingImg:$('<img />',{'src':'/ajax-comments/images/loading.gif','width':'16','height':'16'}),
		};
		let settings = $.extend(defaultSettings,options);
		let commentsObj = this;
		var replyFormObj = null;
		let loading = $('<div />',{'text':'Loading Comments ','class':'text-center'}).append(settings.loadingImg);
		if(settings.module==null || settings.module_id==null){
			throw new Error("The moodule must be defined ");
		}
		if(settings._token==null){
			throw new Error("Laraval token must be defined ");
		}
		
		loadComments();
		function loadComments(setup=true){
			
			let postData = "module="+settings.module+"&module_id="+settings.module_id+"&_token="+settings._token;
			postData += "&displayLimit="+settings.displayLimit; 
			postData += "&offset="+settings.displayOffset;
			postData += "&setup="+setup;
			postData += "&allow_rating="+settings.allowRating
			
			
			$.ajax({
				'async':true,
				type:'POST',
				url:'/comments/get_comments',
				data:postData,
				beforeSend:function(){
					if(setup)
						commentsObj.html(loading);
				},
				success:function(response){
					if(setup){
						commentsObj.html(response);
					}else{
						commentsObj.find('#comment-list').append(response);
						settings.loadingImg.hide();
						
					}
				},
				complete:function(){
					loading.remove();
				},
				fail:function(){
					//commentsObj.html('Failed to load the comments');
				}
			})
		}
		//Load more comments handler
		$(document.body).on('click','#load-more-comments',function(){
			settings.displayOffset+=settings.displayLimit; 
			if(settings.displayOffset<$(this).attr('data-comments-count')){
				$(this).append(settings.loadingImg);
				settings.loadingImg.show()
				loadComments(false);
			}else{
				$(this).hide();
			}
		});
		
		//Vote Comments
		$(document.body).on('click','.up-vote-btn,.down-vote-btn',function(){
			
			let commentId = $(this).attr('data-comment-id');
			let ratingCount = Number($('#comment_'+commentId).find('.rating-count').html());
			let vote; 
			if($(this).attr('class')=='up-vote-btn'){
				vote = 'up_vote';
				if($(this).parent().find('.down-vote-btn').hasClass('active'))
					ratingCount+=2;
				else
					ratingCount++;
			}else{
				vote = 'down_vote';
				if($(this).parent().find('.up-vote-btn').hasClass('active'))
					ratingCount-=2;
				else
					ratingCount--;
			}
			$('#comment_'+commentId).find('.rating-count').html(ratingCount);
		
			$(this).parent().find(':disabled').prop('disabled',false);
			$(this).parent().find('.active').removeClass('active');
			$(this).prop('disabled',true);
			$(this).addClass('active');
			$.ajax({
				type:'POST',
				url:'/comments/vote_comment',
				data:"comment_id="+commentId+"&vote="+vote+"&_token="+settings._token
			});
		});
		
		//Activate the submit button when a minimum charachters are typed; 
		$(document.body).on('change keyup paste','textarea[name="comment-content"],textarea[name="reply-content"]',function(){
			if($(this).val().trim().length>settings.minChar){
				$(this).parent().find('button[name="comment-submit"],button[name="reply-submit"],button[name="comment-save"]').attr('disabled',false);
			}else{
				$(this).parent().find('button[name="comment-submit"],button[name="reply-submit"],button[name="comment-save"]').attr('disabled',true);
			}
		});
		//New comment submit handler
		$(document.body).on('click','button[name="comment-submit"]',function(){
			let commentContent = $('textarea[name="comment-content"]').val().trim();
			$.ajax({
				'async':true,
				type:'POST',
				url:'/comments/new_comment',
				data:"module="+settings.module+"&module_id="+settings.module_id+"&comment_content="+commentContent+"&allow_rating="+settings.allowRating+"&_token="+settings._token,
				beforeSend:function(){
					$('textarea[name="comment-content"]').attr('disabled',true);
				},
				success:function(response){
					commentsObj.find('#comment-list').prepend(response);
					$('button[name="comment-submit"]').attr('disabled',true);
					$('textarea[name="comment-content"]').val('');
				},
				complete:function(){
					$('textarea[name="comment-content"]').attr('disabled',false);
				}
			})
		
			return false;
		});
		//Delete comment handler
		$(document.body).on('click','.comment-delete',function(){
			let commentId = $(this).attr('data-comment-id');
			buttonObj = $(this);
			$.ajax({
				'async':true,
				type:'POST',
				url:'/comments/delete_comment',
				dataType:'JSON',
				data:"comment_id="+commentId+"&_token="+settings._token,
				beforeSend:function(){
					buttonObj.after(settings.loadingImg);
				},
				success:function(response){
					if(response.status=='success'){
						$('#comment_'+commentId).fadeOut('normal',function(){
							$(this).remove();
						});
					}
				},
				complete:function(){
					settings.loadingImg.remove();
				}
			})
			
		});
		
		function getReplyForm(){
			var tmp = null;
			$.ajax({
				type:"GET",
				'async': false,
				url:"/comments/reply_form",
				success:function(response){
					 tmp=$(response);
				}
			});
			
			return tmp;
		};
		
		//Show the reply form 
		$(document.body).on('click','button[name="reply-add"]',function(){
			let parentId = $(this).attr('data-parent-id');
			buttonObj = $(this);
			if(replyFormObj==null){
				replyFormObj = getReplyForm();
			}
			replyFormObj.find('form').attr('data-parent-id',parentId).show();
			replyFormObj.find('textarea').attr('data-parent-id',parentId).val('').attr('disabled',false);
			replyFormObj.find('button[name="reply-submit"]').attr('data-parent-id',parentId).attr('disabled',true);
			
			$('#comment_'+parentId).find('.replies_list').append(replyFormObj);
			settings.loadingImg.remove();
			$('button[name="reply-add"]').show();
			$(this).hide();
		})
		//Reply submit handler
		$(document.body).on('click','button[name="reply-submit"]',function(){
			let parentId = $(this).attr('data-parent-id');
			let replyContent = $('textarea[data-parent-id="'+parentId+'"]').val();
			$.ajax({
				type:'POST',
				url:'/comments/new_comment',
				data:"module="+settings.module+"&module_id="+settings.module_id+"&parent_id="+parentId+"&is_reply=true&comment_content="+replyContent+"&_token="+settings._token,
				beforeSend:function(){
					$('textarea[data-parent-id="'+parentId+'"]').attr('disabled',true);
				},
				success:function(response){
					$('#comment_'+parentId).find('.replies_list').append(response);
					$('form[data-parent-id="'+parentId+'"]').hide();
					$('#comment_'+parentId).find('button[name="reply-add"]').show();
				},
				complete:function(){
					$('textarea[data-parent-id="'+parentId+'"]').attr('disabled',false);
				}
			})
			return false;
		});
		//Create comment edit form
		function getEditForm(commentId,commentContent){
			let textarea_=$('<textarea></textarea>',{'class':'form-control mb-10','data-comment-id':commentId,'text':commentContent,'name':'comment-content'});
			let submitButton=$('<button></button>',{'type':'button','class':'btn btn-sm btn-primary pull-left mt-1 comment-save','text':'حفظ','name':'comment-save','data-comment-id':commentId});
			return $('<form></form>',{'method':'POST','data-comment-id':commentId}).append(textarea_).append(submitButton);
		}
		//Show the comment edit form
		$(document.body).on('click','.comment-edit',function(){
			let commentId = $(this).attr('data-comment-id');
			let commentContent = $('#comment_'+commentId).find('.comment-content').html().trim();
			$('#comment_co_'+commentId).html(getEditForm(commentId,commentContent));
			$(this).hide();
		})
		//Save the comment handler
		$(document.body).on('click','button[name="comment-save"]',function(){
			let commentId = $(this).attr('data-comment-id');
			let commentContent = $('textarea[data-comment-id="'+commentId+'"]').val();
			$.ajax({
				type:'POST',
				url:'/comments/save_comment',
				data:"comment_id="+commentId+"&comment_content="+commentContent+"&_token="+settings._token,
				beforeSend:function(){
					$('textarea[data-comment-id="'+commentId+'"]').attr('disabled',true);
				},
				success:function(response){
					 $('form[data-comment-id="'+commentId+'"]').remove();
					 $('#comment_co_'+commentId).html(commentContent);
					 $('.comment-edit[data-comment-id='+commentId+']').show();
				},
				complete:function(){
					 $('textarea[data-comment-id="'+commentId+'"]').attr('disabled',false);
				}
			})
			return false;
		});
		
		return this;
		
	}
})(jQuery)
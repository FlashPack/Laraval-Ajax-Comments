<?php

Route::post('/comments/get_comments','CommentsController@get_comments');
Route::post('comments/new_comment','CommentsController@new_comment');
Route::post('comments/save_comment','CommentsController@save_comment');
Route::post('comments/delete_comment','CommentsController@delete_comment');
Route::get('comments/reply_form','CommentsController@reply_form');
Route::post('comments/vote_comment','CommentsController@vote_comment');


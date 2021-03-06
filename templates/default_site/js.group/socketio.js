var CHATEADORA = 6;
var newComments = 0;
var member_group = {member_group};

var timeNotification = 60 * 1000 * 5;

var pageTitle = document.title;
function addNotificationsHeader(num) {
  document.title = "(" + num + ") " + pageTitle;
}

function removeNotificationsHeader() {
  document.title = pageTitle;
}

function viewComment(commentId) {
  var $comment = $("#post #comment-" + commentId);
  if ($comment.length > 0) {
    $("html, body").animate({scrollTop: $comment.offset().top -150}, 1000);
    $comment.addClass("highlighter").delay(3000).queue(function(){
      $(this).removeClass("highlighter").dequeue();
    });
  }
}

function log( text ) {
  console.log(text);
}

var socket = io("{exp:routes:new_socket_url}");

socket.on('status', function (data) {
	var response = JSON.parse(data);
	if (notInWall() && member_group !== CHATEADORA) return;

  $.ajax({
      method: 'GET',
      url: '{site_url}wall/new_post/' + response.post_id + '/{member_group}'
  })
  .done(function(post) {
      $("#new_post").prepend(post);
      formatLinks && formatLinks();
      var count = $("#new_post").find(".post-1").length;
      $("#new_post_count").text(count);
      addNotificationsHeader(count);
      
      if (notInWall()) {
        $("#alert-publication").on('click', function(e) {
          location.href = '{site_url}';
        });
      }
      $("#alert-publication").fadeIn().delay(5000);
  });
});

socket.on('comment', function (data) {
	var response = JSON.parse(data);
	$.ajax({
    method: "GET",
    url: "{site_url}wall/new_comment/" + response.comment_id
	})
	.done(function(comment) {
    var divComment = $("div[data-comment-container-post-id=" + response.post_id +"]");
    divComment.append(comment);
    $("span[data-comment-post-id=" + response.post_id +"]").text(response.total);
    formatLinks &&  formatLinks();
    if (notInWall() && member_group !== CHATEADORA && divComment.length > 0) {
      newComments++;
      $('a.scroll').unbind('click');
      $("#new_post_count").text(newComments);
      $("#new_event_description").html(" nuevo(s) comentario!");
      addNotificationsHeader(newComments);
      $("#alert-publication").fadeIn().delay(1000);
      $("#alert-publication").on('click', function(e){
        e.preventDefault();
        $(document.body).animate({'scrollTop':   $('#comment-' + response.comment_id).offset().top - 150}, 1000);
        $("#alert-publication").fadeOut();
        removeNotificationsHeader();
        newComments = 0;
      });
    }

    // Si el usuario que hizo el comentario no es un chateadora y el usuario en sesion es una chateadora
    if (response.member_group !== CHATEADORA && member_group === CHATEADORA && $("#post #comment-" + response.comment_id).length > 0) {
      alertify.log("<span style='cursor: pointer' onclick='viewComment(" + response.comment_id + ")'>" + response.comment_member + " comento una publicación</span>", "success", timeNotification);
    }
	});
});

socket.on('statusSolvedChange', function (data) {
	var response = JSON.parse(data);
  updateSolvedStatus(response.post_id, response.solved);
});

socket.on('like', function (data) {
	var response = JSON.parse(data);
	$("span[data-like-post-id=" + response.post_id + "]").text(response.total);
});

function send(data) {
	var result = JSON.parse(data);
	socket.emit(result.action, data);
}

function notInWall() {
	return location.pathname.indexOf('perfil') >= 0 || location.pathname.indexOf('servicios') >= 0;
}
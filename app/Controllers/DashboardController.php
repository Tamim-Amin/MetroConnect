<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Post;

class DashboardController extends Controller {
    public function index() { //load post
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $posts = Post::all();
        $this->view('dashboard.php', ['user' => $user, 'posts' => $posts]);
    }

    public function createPost() {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $content = trim($_POST['content'] ?? '');

        if ($content === '' && empty($_FILES['image']['name'])) {
            echo "Post can't be empty.";
            return;
        }

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $file = $_FILES['image'];

            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if ($file['error'] !== UPLOAD_ERR_OK) { echo "Image upload error."; return; }
            $mime = mime_content_type($file['tmp_name']);
            if (!in_array($mime, $allowed, true)) { echo "Only JPG, PNG, GIF, WEBP allowed."; return; }
            if ($file['size'] > 4 * 1024 * 1024) { echo "Image too large (max 4MB)."; return; }


            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_', true) . '.' . $ext;
            $dest = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) { echo "Failed to save uploaded image."; return; }
            $imagePath = '/uploads/' . $filename;
        }

        Post::create((int)$user['id'], $content, $imagePath);
        header('Location: /dashboard');
        exit;
    }

    public function editForm() {
        $user = Session::get('user');
        if (!$user) { header('Location: /login'); exit; }

        $id = (int)($_GET['id'] ?? 0);
        $post = Post::find($id);
        if (!$post) { echo "Post not found."; return; }

        // ownership check
        if ((int)$post['user_id'] !== (int)$user['id']) { echo "You can only edit your own posts."; return; }

        // 24-hour window
        $created = strtotime($post['created_at']);
        if (time() - $created > 24 * 60 * 60) { echo "Edit window expired (24 hours)."; return; }

        $this->view('dashboard_edit.php', ['user' => $user, 'post' => $post]);
    }

    public function editPost() {
        $user = Session::get('user');
        if (!$user) { header('Location: /login'); exit; }

        $id = (int)($_POST['id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($content === '') { echo "Post content can't be empty."; return; }

        $post = Post::find($id);
        if (!$post) { echo "Post not found."; return; }
        if ((int)$post['user_id'] !== (int)$user['id']) { echo "You can only edit your own posts."; return; }

        $created = strtotime($post['created_at']);
        if (time() - $created > 24 * 60 * 60) { echo "Edit window expired (24 hours)."; return; }

        Post::updateContent($id, $content);
        header('Location: /dashboard');
        exit;
    }
    public function showEditForm() {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login'); exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo "Invalid post id."; return;
        }

        $post = \App\Models\Post::findById($id);
        if (!$post) {
            echo "Post not found."; return;
        }

        // ownership check
        if ((int)$post['user_id'] !== (int)$user['id']) {
            echo "You are not allowed to edit this post."; return;
        }

        // time limit: 24 hours
        $created = new \DateTime($post['created_at']);
        $now = new \DateTime();
        $diffHours = ($now->getTimestamp() - $created->getTimestamp()) / 3600;
        if ($diffHours > 24) {
            echo "Editing period (24 hours) has expired."; return;
        }

        $this->view('post_edit.php', ['user' => $user, 'post' => $post]);
    }

    public function updatePost() {
        $user = Session::get('user');
        if (!$user) {
            header('Location: /login'); exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($id <= 0) { echo "Invalid post id."; return; }
        if ($content === '') { echo "Content can't be empty."; return; }

        $post = \App\Models\Post::findById($id);
        if (!$post) { echo "Post not found."; return; }
        if ((int)$post['user_id'] !== (int)$user['id']) { echo "Not authorized."; return; }

        // enforce 24-hour edit window
        $created = new \DateTime($post['created_at']);
        $now = new \DateTime();
        $diffHours = ($now->getTimestamp() - $created->getTimestamp()) / 3600;
        if ($diffHours > 24) {
            echo "Editing period (24 hours) has expired."; return;
        }

        // update
        $ok = \App\Models\Post::update($id, $content);
        if ($ok) {
            header('Location: /dashboard'); exit;
        } else {
            echo "Failed to update post.";
        }
    }


// inside App\Controllers\DashboardController class
    public function toggleLike() {
        // ensure session is started
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        // quick helper to respond and exit
        $respond = function(int $code, array $payload) {
            http_response_code($code);
            echo json_encode($payload);
            exit;
        };

        // user check
        $user = \App\Core\Session::get('user');
        if (!$user) {
            $respond(401, ['ok' => false, 'error' => 'Unauthorized: not logged in', 'received' => $_POST]);
        }

        // read incoming values
        // Accept CSRF either via header X-CSRF-Token or via POST param (for debug)
        $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? '');
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

        // debug info (will be sent if something bad)
        $debug = [
            'session_csrf' => $_SESSION['csrf'] ?? null,
            'sent_csrf' => $csrfHeader,
            'post_id' => $postId,
            'session_user' => $user
        ];

        // csrf check
        if (empty($_SESSION['csrf']) || $csrfHeader !== $_SESSION['csrf']) {
            error_log('Like: invalid csrf. Debug: ' . json_encode($debug));
            $respond(400, ['ok' => false, 'error' => 'Invalid CSRF token', 'debug' => $debug]);
        }

        // validate post id
        if ($postId <= 0) {
            error_log('Like: invalid post id. Debug: ' . json_encode($debug));
            $respond(400, ['ok' => false, 'error' => 'Invalid post id', 'debug' => $debug]);
        }

        // ensure post exists
        try {
            $post = \App\Models\Post::findById($postId);
        } catch (\Exception $e) {
            error_log('Like: Post find error: ' . $e->getMessage());
            $respond(500, ['ok' => false, 'error' => 'Server error finding post']);
        }
        if (!$post) {
            $respond(404, ['ok' => false, 'error' => 'Post not found', 'debug' => $debug]);
        }

        // toggle like
        try {
            $liked = \App\Models\Like::toggle((int)$user['id'], $postId);
            $count = \App\Models\Like::countForPost($postId);
            $respond(200, ['ok' => true, 'liked' => (bool)$liked, 'count' => (int)$count]);
        } catch (\Exception $e) {
            error_log('Like toggle error: ' . $e->getMessage());
            $respond(500, ['ok' => false, 'error' => 'Server error toggling like']);
        }
    }


    public function createComment() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = \App\Core\Session::get('user');
        if (!$user) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
            return;
        }

        // CSRF
        $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($_SESSION['csrf']) || $csrfHeader !== $_SESSION['csrf']) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        $content = trim((string)($_POST['content'] ?? ''));

        if ($postId <= 0 || $content === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid input']);
            return;
        }

        if (mb_strlen($content) > 1000) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Comment too long (max 1000 chars)']);
            return;
        }

        // Ensure post exists
        $post = \App\Models\Post::findById($postId);
        if (!$post) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Post not found']);
            return;
        }

        // Optional: validate parent belongs to same post
        if ($parentId) {
            $parent = \App\Models\Comment::findById($parentId);
            if (!$parent || (int)$parent['post_id'] !== $postId) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Invalid parent comment']);
                return;
            }
        }

        try {
            $commentId = \App\Models\Comment::create($postId, (int)$user['id'], $content, $parentId);
            $newComment = \App\Models\Comment::findById($commentId);
            // attach user info
            $newComment['name'] = $user['name'];
            $newComment['email'] = $user['email'];
            $newComment['avatar_path'] = $user['avatar_path'] ?? null;

            echo json_encode(['ok' => true, 'comment' => $newComment]);
        } catch (\Exception $e) {
            error_log('Create comment error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Server error']);
        }
    }


}
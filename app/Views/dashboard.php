<?php
use App\Models\Like;
use App\Models\Comment;

$title = 'Dashboard • AuthBoard';
ob_start();
?>

<?php
// Require login
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$user = $_SESSION['user'];
?>
    <!-- ========== CREATE POST ========== -->
    <div class="bg-white p-5 rounded-xl card-shadow fade-up show">
        <form id="postForm" method="POST" action="/post" enctype="multipart/form-data" class="space-y-4">
        <textarea name="content" rows="3" placeholder="What's happening?"
                  class="w-full resize-none border rounded p-3"></textarea>

            <div class="flex items-center gap-3">
                <label class="cursor-pointer text-sm text-slate-600 flex items-center gap-2">
                    <input type="file" name="image" id="imageInput" accept="image/*"
                           class="hidden"
                           onchange="handleImagePreview(this, document.getElementById('imgPreview'))" />
                    <span class="px-3 py-2 bg-slate-100 rounded">Attach image</span>
                </label>

                <div id="imgPreview" class="hidden"></div>

                <button class="ml-auto bg-indigo-600 text-white px-4 py-2 rounded">Post</button>
            </div>
        </form>
    </div>

    <!-- ========== FEED ========== -->
    <div id="feed" class="space-y-4 mt-6">

        <?php if (empty($posts)): ?>
            <div class="text-center text-slate-500">No posts yet — be the first to post.</div>
        <?php endif; ?>

        <?php

        if (!function_exists('render_comments')) {
            function render_comments(array $comments, int $depth = 0) {
                foreach ($comments as $c) {
                    $indent = $depth * 16; // px

                    echo '<div class="mb-3" style="margin-left:' . $indent . 'px" data-comment-id="'. (int)$c['id'] .'">';
                    echo '<div class="text-sm"><strong>' . htmlspecialchars($c['name']) . '</strong>';
                    echo ' <span class="text-xs text-slate-500">• ' . htmlspecialchars($c['created_at']) . '</span></div>';
                    echo '<div class="text-sm mt-1">' . nl2br(htmlspecialchars($c['content'])) . '</div>';
                    echo '<div class="text-xs mt-1"><a href="#" class="reply-link text-indigo-600" data-comment-id="' . (int)$c['id'] . '">Reply</a></div>';

                    if (!empty($c['children'])) {
                        render_comments($c['children'], $depth + 1);
                    }

                    echo '</div>';
                }
            }
        }
        ?>

        <?php foreach ($posts as $post): ?>
            <?php
            // like info
            $likeCount = Like::countForPost((int)$post['id']);
            $userLiked = Like::userLiked((int)$user['id'], (int)$post['id']);

            // comments
            $flatComments = Comment::fetchByPost((int)$post['id']);
            $treeComments = Comment::buildTree($flatComments);
            ?>

            <article class="bg-white p-5 rounded-xl card-shadow fade-up show">
                <div class="flex items-start gap-4">

                    <!-- avatar -->
                    <div class="w-12 h-12 bg-indigo-600 rounded text-white flex items-center justify-center font-bold">
                        <?= strtoupper(substr($post['name'], 0, 2)) ?>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-semibold"><?= htmlspecialchars($post['name']) ?></div>
                                <div class="text-xs text-slate-500">
                                    <?= htmlspecialchars($post['email']) ?> •
                                    <span><?= htmlspecialchars($post['created_at']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

                        <?php if (!empty($post['image_path'])): ?>
                            <div class="mt-3 rounded overflow-hidden">
                                <img src="<?= htmlspecialchars($post['image_path']) ?>" class="w-full object-cover max-h-80">
                            </div>
                        <?php endif; ?>

                        <!-- ========== ACTIONS: LIKE + COMMENT + EDIT ========== -->
                        <div class="mt-3 flex items-center gap-4 text-sm text-slate-500">

                            <!-- Like Button -->
                            <button
                                    class="like-btn flex items-center gap-2 hover:text-indigo-600"
                                    type="button"
                                    data-post-id="<?= (int)$post['id'] ?>"
                                    data-liked="<?= $userLiked ? '1' : '0' ?>"
                                    aria-pressed="<?= $userLiked ? 'true' : 'false' ?>"
                            >
                                <svg class="heart-icon h-4 w-4"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="<?= $userLiked ? 'currentColor' : 'none' ?>"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 1 1 6.364 6.364L12 20.364 4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/>
                                </svg>

                                <span class="like-count"><?= $likeCount ?></span>
                            </button>

<!--                            <button class="hover:text-indigo-600">Comment</button>-->

                            <button class="comment-toggle hover:text-indigo-600"
                                    data-post-id="<?= (int)$post['id'] ?>">
                                Comment
                            </button>

                            <a href="/post/edit?id=<?= (int)$post['id'] ?>" class="hover:text-indigo-600">
                                Edit
                            </a>

                            <?php if (!empty($post['edited_at']) && $post['edited_at'] !== $post['created_at']): ?>
                                <span class="text-xs text-slate-400">• edited</span>
                            <?php endif; ?>

                        </div>

                        <!-- COMMENT COMPOSER (hidden by default) -->
                        <div class="comment-wrapper mt-4 hidden" id="comment-box-<?= (int)$post['id'] ?>">
                            <form class="comment-form" data-post-id="<?= (int)$post['id'] ?>" onsubmit="return false;">
                                <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                <input type="hidden" name="parent_id" value="">
                                <div class="flex gap-2 items-start">
            <textarea name="content" rows="2" placeholder="Write a comment..."
                      class="w-full border rounded p-2"></textarea>
                                    <button type="button"
                                            class="btn-comment bg-indigo-600 text-white px-3 py-2 rounded">
                                        Reply
                                    </button>
                                </div>
                            </form>
                        </div>


                        <!-- ========== COMMENT LIST ========== -->
                        <div id="comments-for-post-<?= (int)$post['id'] ?>" class="mt-4">
                            <?php render_comments($treeComments); ?>
                        </div>

                    </div>
                </div>
            </article>

        <?php endforeach; ?>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';

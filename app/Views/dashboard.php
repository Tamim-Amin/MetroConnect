<?php
use App\Models\Like;
$title = 'Dashboard • AuthBoard';
ob_start();
?>
<?php
// If you want to show the dashboard even when not logged in (for debug),
// comment this redirect check. Normally we require login.
if (empty($user)) {
    // not logged in — redirect to login
    header('Location: /login');
    exit;
}
?>
    <div class="lg:grid lg:grid-cols-3 gap-6">
        <aside class="hidden lg:block">
            <div class="bg-white p-4 rounded-xl card-shadow fade-up show">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded bg-indigo-600 text-white flex items-center justify-center font-bold"><?= strtoupper(substr($user['name'],0,2)) ?></div>
                    <div>
                        <div class="font-semibold"><?= htmlspecialchars($user['name']) ?></div>
                        <div class="text-xs text-slate-500"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-sm">
                    <a href="/dashboard" class="block px-3 py-2 rounded hover:bg-slate-50">Home</a>
                    <a href="/logout" class="block px-3 py-2 rounded text-red-600 hover:bg-slate-50">Logout</a>
                </div>
            </div>
        </aside>

        <section class="col-span-2 space-y-6">
            <div class="bg-white p-5 rounded-xl card-shadow fade-up show">
                <form id="postForm" method="POST" action="/post" enctype="multipart/form-data" class="space-y-4">
                    <label for="content" class="sr-only">Post content</label>
                    <textarea id="content" name="content" rows="3" placeholder="What's happening?" class="w-full resize-none border rounded p-3"></textarea>

                    <div class="flex items-center gap-3">
                        <label for="imageInput" class="flex items-center gap-2 cursor-pointer text-sm text-slate-600">
                            <input id="imageInput" type="file" name="image" accept="image/*" class="hidden" onchange="handleImagePreview(this, document.getElementById('imgPreview'))" />
                            <span class="px-3 py-2 bg-slate-100 rounded inline-flex items-center gap-2">Attach image</span>
                        </label>

                        <div id="imgPreview" class="hidden"></div>

                        <div class="ml-auto flex items-center gap-3">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Post</button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="feed" class="space-y-4">
                <?php if (empty($posts)): ?>
                    <div class="text-center text-slate-500">No posts yet — be the first to post.</div>
                <?php endif; ?>

                <?php foreach ($posts as $post): ?>
                    <article class="bg-white p-5 rounded-xl card-shadow fade-up show">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded bg-indigo-600 text-white flex items-center justify-center font-bold"><?= strtoupper(substr($post['name'],0,2)) ?></div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-semibold"><?= htmlspecialchars($post['name']) ?></div>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars($post['email']) ?> • <span><?= htmlspecialchars($post['created_at']) ?></span></div>
                                    </div>
                                </div>

                                <div class="mt-3 text-slate-800 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

                                <?php if (!empty($post['image_path'])): ?>
                                    <div class="mt-3 rounded overflow-hidden">
                                        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="post image" class="w-full object-cover max-h-80"/>
                                    </div>
                                <?php endif; ?>
                                <?php
                                // count and state
                                $likeCount = Like::countForPost((int)$post['id']);
                                $userLiked = false;
                                if (!empty($user)) {
                                    $userLiked = Like::userLiked((int)$user['id'], (int)$post['id']);
                                }
                                ?>
                                <div class="mt-3 flex items-center gap-4 text-sm text-slate-500">
                                    <button
                                            class="like-btn flex items-center gap-2 hover:text-indigo-600"
                                            data-post-id="<?= (int)$post['id'] ?>"
                                            data-liked="<?= $userLiked ? '1' : '0' ?>"
                                            aria-pressed="<?= $userLiked ? 'true' : 'false' ?>"
                                            aria-label="Like post"
                                            type="button"
                                    >
                                        <svg class="heart-icon h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                             fill="<?= $userLiked ? 'currentColor' : 'none' ?>" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 1 1 6.364 6.364L12 20.364 4.318 12.682a4.5 4.5 0 0 1 0-6.364z"/>
                                        </svg>
                                        <span class="like-count"><?= $likeCount ?></span>
                                    </button>

                                    <button class="hover:text-indigo-600">Comment</button>
                                    <?php $canEdit = false; ?>
                                        <a href="/post/edit?id=<?= (int)$post['id'] ?>" class="hover:text-indigo-600">Edit</a>
                                    <?php  ?>
                                    <?php
                                    if (!empty($post['edited_at']) && $post['edited_at'] !== $post['created_at']): ?>
                                        <span class="text-xs text-slate-400">• edited</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="hidden lg:block">
            <div class="bg-white p-4 rounded-xl card-shadow fade-up show">
                <div class="text-sm text-slate-600 font-semibold mb-2">Tips</div>
                <ul class="text-sm space-y-2 text-slate-500">
                    <li>Upload images up to 4MB.</li>
                    <li>Keep posts friendly and clean.</li>
                    <li>Use this prototype for demos and learning.</li>
                </ul>
            </div>
        </aside>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';

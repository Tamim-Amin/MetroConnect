<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) {
    try { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
    catch (Exception $e) { $_SESSION['csrf'] = uniqid('', true); }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <title><?= htmlspecialchars($title ?? 'AuthBoard') ?></title>


    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(6px); }
        .fade-up { transform: translateY(6px); opacity: 0; transition: all .36s cubic-bezier(.2,.9,.2,1); }
        .fade-up.show { transform: none; opacity: 1; }
        .card-shadow { box-shadow: 0 6px 18px rgba(22,28,45,0.06); }
        @media (min-width: 1024px) { .content-max { max-width: 980px; } }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<div class="min-h-screen flex flex-col">
    <header class="bg-white border-b">
        <div class="mx-auto px-4 py-3 flex items-center justify-between content-max w-full">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-blue-400 text-white font-bold rounded-lg">AB</div>
                <div>
                    <div class="text-lg font-semibold">AuthBoard</div>
                    <div class="text-xs text-slate-500">Simple social feed — learn-by-building</div>
                </div>
            </div>

            <nav class="flex items-center gap-4 text-sm">
                <?php if (!empty($_SESSION['user'])): ?>
                    <a href="/dashboard" class="px-3 py-2 rounded hover:bg-slate-100">Dashboard</a>
                    <a href="/logout" class="px-3 py-2 text-red-600 rounded hover:bg-slate-100">Logout</a>
                <?php else: ?>
                    <a href="/login" class="px-3 py-2 rounded hover:bg-slate-100">Login</a>
                    <a href="/register" class="px-3 py-2 bg-indigo-600 text-white rounded hover:brightness-95">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="flex-1 py-8 px-4">
        <div class="mx-auto content-max">
            <?php echo $content; ?>
        </div>
    </main>

    <footer class="text-center text-sm text-slate-500 py-6">
        <small>AuthBoard — demo project • Built for learning</small>
    </footer>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Submit comment (works for any .comment-form)
        document.querySelectorAll('.comment-form').forEach(form => {
            const postId = form.dataset.postId || form.querySelector('input[name="post_id"]').value;
            const submitBtn = form.querySelector('.btn-comment');
            submitBtn.addEventListener('click', async () => {
                const contentEl = form.querySelector('textarea[name="content"]');
                const parentEl = form.querySelector('input[name="parent_id"]');
                const content = contentEl.value.trim();
                if (!content) return alert('Comment cannot be empty.');

                // optimistic UI: disable button
                submitBtn.disabled = true;

                const fd = new FormData();
                fd.append('post_id', postId);
                fd.append('parent_id', parentEl.value || '');
                fd.append('content', content);

                try {
                    const res = await fetch('/post/comment', {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-CSRF-Token': csrf }
                    });
                    const json = await res.json();
                    if (json.ok) {
                        // append the new comment to the relevant comments-list
                        const list = document.querySelector('#comments-for-post-' + postId);
                        if (list) {
                            // create element for the new comment
                            const c = json.comment;
                            const wrapper = document.createElement('div');
                            wrapper.className = 'comment-item mb-3';
                            wrapper.dataset.commentId = c.id;
                            // if parent_id present, indent accordingly
                            const indent = parentEl.value ? 12 : 0;
                            wrapper.style.marginLeft = (parentEl.value ? indent + 'px' : '0px');

                            wrapper.innerHTML = '<div class="text-sm"><strong>' + escapeHtml(c.name) +
                                '</strong> <span class="text-xs text-slate-500">• ' + escapeHtml(c.created_at) +
                                '</span></div><div class="text-sm mt-1">' + nl2br(escapeHtml(c.content)) +
                                '</div><div class="text-xs mt-1"><a href="#" class="reply-link text-indigo-600" data-comment-id="' + c.id + '">Reply</a></div>';
                            // if it's a reply, try to insert after parent; otherwise append
                            if (parentEl.value) {
                                // find parent node
                                const parentNode = list.querySelector('[data-comment-id="' + parentEl.value + '"]');
                                if (parentNode && parentNode.nextSibling) {
                                    parentNode.parentNode.insertBefore(wrapper, parentNode.nextSibling);
                                } else if (parentNode) {
                                    parentNode.parentNode.appendChild(wrapper);
                                } else {
                                    list.appendChild(wrapper);
                                }
                            } else {
                                list.appendChild(wrapper);
                            }

                            // clear and reset composer
                            contentEl.value = '';
                            parentEl.value = '';
                            submitBtn.disabled = false;
                        }
                    } else {
                        alert(json.error || 'Failed to post comment.');
                        submitBtn.disabled = false;
                    }
                } catch (err) {
                    console.error(err);
                    alert('Network error while posting comment.');
                    submitBtn.disabled = false;
                }
            });
        });

        // Reply link behavior: set parent_id of nearest comment composer
        document.addEventListener('click', function (e) {
            if (e.target.matches('.reply-link')) {
                e.preventDefault();
                const commentId = e.target.getAttribute('data-comment-id');
                // find the closest .comment-form for this post
                let node = e.target;
                // climb up to find the post container (we used parent markup, but easiest: find id)
                const postContainer = e.target.closest('[id^="comments-for-post-"]');
                if (!postContainer) return;
                const postId = postContainer.id.replace('comments-for-post-', '');
                const form = document.querySelector('.comment-form[data-post-id="' + postId + '"]');
                if (!form) return;
                form.querySelector('input[name="parent_id"]').value = commentId;
                const textarea = form.querySelector('textarea[name="content"]');
                textarea.focus();
            }
        });

        // helpers
        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
        function nl2br(str) {
            return str.replace(/\n/g, '<br/>');
        }
    });
</script>
<script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function setButtonState(btn, liked, count) {
            btn.setAttribute('data-liked', liked ? '1' : '0');
            btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
            const icon = btn.querySelector('.heart-icon');
            if (icon) icon.setAttribute('fill', liked ? 'currentColor' : 'none');
            const span = btn.querySelector('.like-count');
            if (span) span.textContent = count;
        }

        // Attach via event delegation so dynamically added posts work as well
        document.addEventListener('click', async function (e) {
            const btn = e.target.closest('.like-btn');
            if (!btn) return;

            e.preventDefault();
            const postId = btn.getAttribute('data-post-id') || null;
            if (!postId) {
                console.error('Like click: no postId on button', btn);
                alert('Internal error: missing post id.');
                return;
            }

            const currentlyLiked = btn.getAttribute('data-liked') === '1';
            const currentCount = parseInt(btn.querySelector('.like-count')?.textContent || '0', 10);

            // optimistic UI
            setButtonState(btn, !currentlyLiked, currentlyLiked ? Math.max(currentCount-1,0) : currentCount+1);

            // prepare form data
            const fd = new FormData();
            fd.append('post_id', postId);

            // debug: print what we're about to send
            console.log('Like: sending', { post_id: postId, csrf });

            try {
                const resp = await fetch('/post/like', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-Token': csrf }
                });

                // show network and status for debugging
                console.log('Like: fetch complete. status=', resp.status);

                const json = await resp.json();
                console.log('Like: response json=', json);

                if (!json.ok) {
                    // revert optimistic UI
                    setButtonState(btn, currentlyLiked, currentCount);
                    // show server message if provided
                    const message = json.error || 'Could not update like.';
                    console.warn('Like error:', message, json.debug ?? '');
                    alert(message);
                    return;
                }

                // success — set according to server truth
                setButtonState(btn, json.liked, json.count);

            } catch (err) {
                // revert optimistic UI
                setButtonState(btn, currentlyLiked, currentCount);
                console.error('Like: network or JSON error', err);
                alert('Network error while toggling like. See console for details.');
            }
        });
    })();
</script>

<script>
    // COMMENT SYSTEM UI BEHAVIOR
    (function () {

        // Show comment box when clicking "Comment"
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.comment-toggle');
            if (!btn) return;

            const postId = btn.dataset.postId;
            const box = document.getElementById('comment-box-' + postId);
            if (!box) return;

            box.classList.remove('hidden');
            // Reset parent_id (new top-level comment)
            const form = box.querySelector('form');
            form.querySelector('input[name="parent_id"]').value = '';
            form.querySelector('textarea[name="content"]').focus();
        });

        // When clicking "Reply" under a comment
        document.addEventListener('click', function(e) {
            const replyLink = e.target.closest('.reply-link');
            if (!replyLink) return;

            e.preventDefault();

            const commentId = replyLink.dataset.commentId;

            // Find post container
            const commentsContainer = replyLink.closest('[id^="comments-for-post-"]');
            const postId = commentsContainer.id.replace('comments-for-post-', '');

            // Show comment box
            const box = document.getElementById('comment-box-' + postId);
            box.classList.remove('hidden');

            // Set parent id
            const form = box.querySelector('form');
            form.querySelector('input[name="parent_id"]').value = commentId;

            // Focus
            form.querySelector('textarea[name="content"]').focus();
        });

    })();
</script>


</body>
</html>
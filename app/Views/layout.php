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

        function setButtonState(btn, liked, count) {
            btn.setAttribute('data-liked', liked ? '1' : '0');
            btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
            const icon = btn.querySelector('.heart-icon');
            if (icon) {
                // use attribute fill (SVG) so styling toggles cleanly
                icon.setAttribute('fill', liked ? 'currentColor' : 'none');
            }
            const span = btn.querySelector('.like-count');
            if (span) span.textContent = String(count);
        }

        // Attach handlers to all like buttons
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', async function (e) {
                e.preventDefault();

                const postId = this.getAttribute('data-post-id');
                if (!postId) return;

                const currentlyLiked = this.getAttribute('data-liked') === '1';
                const countEl = this.querySelector('.like-count');
                const currentCount = parseInt(countEl?.textContent || '0', 10);

                // optimistic UI update
                setButtonState(this, !currentlyLiked, currentlyLiked ? Math.max(currentCount - 1, 0) : currentCount + 1);

                const fd = new FormData();
                fd.append('post_id', postId);

                try {
                    const res = await fetch('/post/like', {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-CSRF-Token': csrf } // server reads HTTP_X_CSRF_TOKEN
                    });

                    if (!res.ok) {
                        // non-JSON response or non-2xx
                        throw new Error('Network response not OK: ' + res.status);
                    }

                    const json = await res.json();
                    if (json && json.ok) {
                        setButtonState(this, !!json.liked, Number(json.count || 0));
                    } else {
                        // revert optimistic UI
                        setButtonState(this, currentlyLiked, currentCount);
                        const msg = (json && json.error) ? json.error : 'Could not update like.';
                        alert(msg);
                        console.error('Like error:', json);
                    }
                } catch (err) {
                    // revert UI on error
                    setButtonState(this, currentlyLiked, currentCount);
                    console.error('Network or parsing error while toggling like:', err);
                    alert('Network error while toggling like.');
                }
            });
        });

        // for debugging: log how many like buttons found
        console.log('like buttons attached:', document.querySelectorAll('.like-btn').length);
    });
</script>


</body>
</html>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
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
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.fade-up').forEach((el, i) => {
            setTimeout(()=> el.classList.add('show'), 60 * i);
        });
    });

    function handleImagePreview(input, previewEl) {
        const file = input.files && input.files[0];
        if (!file) { previewEl.innerHTML = ''; previewEl.classList.add('hidden'); return; }
        const reader = new FileReader();
        reader.onload = function(e) {
            previewEl.innerHTML = '<img src="'+e.target.result+'" alt="image preview" class="rounded max-w-full max-h-60 object-contain border"/>';
            previewEl.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
    window.handleImagePreview = handleImagePreview;
</script>
</body>
</html>
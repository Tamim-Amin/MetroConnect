<?php
$title = 'Edit Post • AuthBoard';
ob_start();
?>
    <div class="max-w-2xl mx-auto fade-up bg-white p-6 rounded-xl card-shadow">
        <h2 class="text-lg font-semibold mb-3">Edit post</h2>

        <form method="POST" action="/post/edit" class="space-y-4">
            <input type="hidden" name="id" value="<?= (int)$post['id'] ?>" />
            <div>
                <label class="block text-sm text-slate-600 mb-1">Content</label>
                <textarea name="content" rows="5" class="w-full border rounded p-3"><?= htmlspecialchars($post['content']) ?></textarea>
                <p class="text-xs text-slate-500 mt-1">You can edit within 24 hours of posting.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="/dashboard" class="px-3 py-2 rounded border">Cancel</a>
                <button type="submit" class="ml-auto bg-indigo-600 text-white px-4 py-2 rounded">Save changes</button>
            </div>
        </form>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';

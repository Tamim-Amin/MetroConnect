<?php
$title = 'Edit Post • AuthBoard';
ob_start();
?>
    <div class="max-w-xl mx-auto mt-8">
        <div class="bg-white p-5 rounded-xl card-shadow">
            <form method="POST" action="/post/edit" class="space-y-4">
                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>" />
                <textarea name="content" rows="6" class="w-full border rounded p-3"><?= htmlspecialchars($post['content']) ?></textarea>
                <div class="flex justify-end">
                    <a href="/dashboard" class="mr-2 px-4 py-2 rounded border">Cancel</a>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';

<?php
$title = 'Register • AuthBoard';
ob_start();
?>
    <div class="max-w-2xl mx-auto grid lg:grid-cols-2 gap-8 items-center">
        <div class="fade-up bg-white p-8 rounded-xl card-shadow">
            <h3 class="text-lg font-semibold mb-4">Create your account</h3>

            <form method="POST" action="/register" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Full name</label>
                    <input name="name" required type="text" class="w-full border rounded px-3 py-2"/>
                </div>

                <div>
                    <label class="block text-sm text-slate-600 mb-1">Email</label>
                    <input name="email" required type="email" class="w-full border rounded px-3 py-2"/>
                </div>

                <div>
                    <label class="block text-sm text-slate-600 mb-1">Password</label>
                    <input name="password" required type="password" class="w-full border rounded px-3 py-2"/>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">Already a member? <a href="/login" class="text-indigo-600">Login</a></div>
                    <button type="submit" class="bg-indigo-600 px-4 py-2 rounded text-white">Create account</button>
                </div>
            </form>
        </div>

        <div class="hidden lg:flex items-center justify-center">
            <div class="text-center text-slate-500">
                <div class="text-2xl font-bold mb-2">Join AuthBoard</div>
                <p class="max-w-[260px]">Share quick updates with image support. Modern layout—perfect for demos and small projects.</p>
            </div>
        </div>
    </div>
<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
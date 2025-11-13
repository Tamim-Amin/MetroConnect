<?php
$title = 'Login • AuthBoard';
ob_start();
?>
    <div class="max-w-2xl mx-auto grid lg:grid-cols-2 gap-8 items-center">
        <div class="hidden lg:block">
            <div class="glass p-8 rounded-xl card-shadow">
                <h2 class="text-2xl font-bold mb-2">Welcome back</h2>
                <p class="text-slate-600">Sign in to continue to the AuthBoard demo — see and share updates instantly.</p>


            </div>
        </div>

        <div class="fade-up bg-white p-8 rounded-xl card-shadow">
            <h3 class="text-lg font-semibold mb-4">Login to your account</h3>

            <form method="POST" action="/login" class="space-y-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Email</label>
                    <input name="email" required type="email" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200"/>
                </div>

                <div>
                    <label class="block text-sm text-slate-600 mb-1">Password</label>
                    <input name="password" required type="password" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200"/>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">Don't have an account? <a href="/register" class="text-indigo-600">Sign up</a></div>
                    <button type="submit" class="bg-indigo-600 px-4 py-2 rounded text-white">Sign in</button>
                </div>
            </form>
        </div>
    </div>
<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
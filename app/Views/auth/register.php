<?php
$title = 'Register • AuthBoard';
ob_start();
?>
    <div class="max-w-2xl mx-auto grid lg:grid-cols-2 gap-8 items-center">
        <div class="hidden lg:block">
            <div class="glass p-8 rounded-xl card-shadow">
                <h2 class="text-2xl font-bold mb-2">Join AuthBoard</h2>
                <p class="text-slate-600">Create an account to start posting and interacting with the feed.</p>
            </div>
        </div>

        <div class="fade-up show bg-white p-8 rounded-xl card-shadow">
            <h3 class="text-lg font-semibold mb-4">Create your account</h3>

            <form method="POST" action="/register" class="space-y-4" autocomplete="on">
                <div>
                    <label for="name" class="block text-sm text-slate-600 mb-1">Full name</label>
                    <input id="name" name="name" required type="text" placeholder="Your full name" autocomplete="name"
                           class="w-full border rounded px-3 py-2"/>
                </div>

                <div>
                    <label for="email" class="block text-sm text-slate-600 mb-1">Email</label>
                    <input id="email" name="email" required type="email" placeholder="you@example.com" autocomplete="email"
                           class="w-full border rounded px-3 py-2"/>
                </div>

                <div>
                    <label for="password" class="block text-sm text-slate-600 mb-1">Password</label>
                    <input id="password" name="password" required type="password" placeholder="Choose a password" autocomplete="new-password"
                           class="w-full border rounded px-3 py-2"/>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">Already a member? <a href="/login" class="text-indigo-600">Login</a></div>
                    <button type="submit" class="bg-indigo-600 px-4 py-2 rounded text-white">Create account</button>
                </div>
            </form>
        </div>
    </div>
<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
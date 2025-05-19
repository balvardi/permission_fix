<?php
// ⚠️ این اسکریپت را در روت سایت آپلود کنید و از طریق مرورگر اجرا کنید
// بعد از استفاده حتماً آن را حذف کنید!

echo "<pre>";

// دایرکتوری هدف - __DIR__ به معنای محلی که این فایل در آن قرار دارد
$root = __DIR__; // اگر لازم است، مسیر کامل را بنویسید: '/home/username/public_html'

// فایل‌های مهم که نباید پاک کنید ولی می‌تونید پرمیشن خاصی داشته باشند
$important_files = [
    '.htaccess',
    'configuration.php',
    'web.config', // برای IIS
];

// پوشه‌هایی که نباید تغییر کنند (اختیاری)
$skip_dirs = [
    'cache',
    'logs',
    'tmp'
];

echo "🔧 شروع تنظیم پرمیشن‌ها برای جوملا...\n";
echo "📁 روت: $root\n\n";

function getOctalPermissions($file) {
    return substr(sprintf('%o', fileperms($file)), -4);
}

function setJoomlaPermissions($dir, $skipDirs = [], $importantFiles = []) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        $path = $file->getPathname();

        // رد کردن پوشه‌های مشخص شده
        foreach ($skipDirs as $skipDir) {
            if (strpos($path, DIRECTORY_SEPARATOR . $skipDir . DIRECTORY_SEPARATOR) !== false ||
                basename($path) === $skipDir) {
                echo "🚫 رد شد (پوشه محافظت شده): $path\n";
                continue 2;
            }
        }

        // اگر فایل مهم بود، فقط پرمیشن رو نمایش بده
        if (in_array(basename($path), $importantFiles)) {
            echo "🔒 فایل مهم: $path - پرمیشن فعلی: " . getOctalPermissions($path) . "\n";
            continue;
        }

        if ($file->isDir()) {
            $current = getOctalPermissions($path);
            if ($current !== '0755') {
                chmod($path, 0755);
                echo "📁 پوشه تنظیم شد: $path → 755 (قبلی: $current)\n";
            } else {
                echo "✔️ پوشه درست است: $path → $current\n";
            }
        } else {
            $current = getOctalPermissions($path);
            if ($current !== '0644') {
                chmod($path, 0644);
                echo "📄 فایل تنظیم شد: $path → 644 (قبلی: $current)\n";
            } else {
                echo "✔️ فایل درست است: $path → $current\n";
            }
        }
    }
}

setJoomlaPermissions($root, $skip_dirs, $important_files);

echo "\n✅ تمام پرمیشن‌ها با موفقیت چک و تنظیم شدند.\n";
echo "</pre>";
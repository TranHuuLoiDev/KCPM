<?php
// Autoloader cho thư mục app/
spl_autoload_register(function ($class_name) {
    // Prefix cho namespace của chúng ta
    $prefix = 'App\\';
    
    // Thư mục gốc tương ứng với namespace prefix
    $base_dir = __DIR__ . '/';

    // Kiểm tra xem class có dùng namespace prefix của chúng ta không
    $len = strlen($prefix);
    if (strncmp($prefix, $class_name, $len) !== 0) {
        return;
    }

    // Lấy phần tên class sau prefix
    $relative_class = substr($class_name, $len);

    // Thay thế namespace separator bằng directory separator và thêm .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Nếu file tồn tại thì require nó
    if (file_exists($file)) {
        require $file;
    }
});

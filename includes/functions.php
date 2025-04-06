<?php
/**
 * Common utility functions for DoodleSense AI
 */

/**
 * Sanitize user input to prevent XSS
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a random token
 * 
 * @param int $length Length of the token
 * @return string Random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Format a date to a readable format
 * 
 * @param string $date Date to format
 * @param string $format Format to use
 * @return string Formatted date
 */
function format_date($date, $format = 'M j, Y g:i A') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Format date as relative time (e.g., "2 hours ago")
 * 
 * @param string $date Date to format
 * @return string Relative time string
 */
function time_elapsed_string($date) {
    $timestamp = strtotime($date);
    $current = time();
    $diff = $current - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    }
    
    $intervals = [
        1                => ['minute', 'minutes'],
        60               => ['hour', 'hours'],
        60 * 24          => ['day', 'days'],
        60 * 24 * 7      => ['week', 'weeks'],
        60 * 24 * 30     => ['month', 'months'],
        60 * 24 * 365    => ['year', 'years']
    ];
    
    $value = $diff / 60; // Start with minutes
    
    foreach ($intervals as $multiplier => $names) {
        if ($value < $multiplier) {
            break;
        }
        
        if ($multiplier > 1) {
            $value = $value / $multiplier;
        }
        
        $intervalName = $names[($value > 1) ? 1 : 0];
    }
    
    return round($value) . ' ' . $intervalName . ' ago';
}

/**
 * Get user's drawings from the database
 * 
 * @param int $user_id User ID
 * @param int $limit Maximum number of drawings to return
 * @param int $offset Offset for pagination
 * @return array Drawings or empty array on error
 */

 
function get_user_drawings($user_id, $limit = 0, $offset = 0) {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    try {
        $sql = "SELECT * FROM drawings WHERE user_id = :user_id ORDER BY updated_at DESC";
        $params = [':user_id' => $user_id];

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$limit;
            
            if ($offset > 0) {
                $sql .= " OFFSET :offset";
                $params[':offset'] = (int)$offset;
            }
        }

        $stmt = $conn->prepare($sql);
        
        foreach ($params as $key => &$value) {
            $stmt->bindParam($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $drawings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($drawings as &$drawing) {
            $drawing['image_url'] = $drawing['drawing_data'];
        }

        return $drawings;
    } catch(PDOException $e) {
        error_log("Get Drawings Error: " . $e->getMessage());
        return [];
    }
}

function count_downloaded_drawings($user_id) {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    try {
        $stmt = $conn->prepare(
            "SELECT SUM(downloads) as total FROM drawings WHERE user_id = :user_id"
        );
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['total'] ? (int)$result['total'] : 0;
    } catch(PDOException $e) {
        error_log("Count Downloads Error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get user activities
 * 
 * @param int $user_id User ID
 * @param int $limit Maximum number of activities to return
 * @return array Activities or empty array on error
 */
function get_user_activities($user_id, $limit = 10) {
    $sql = "SELECT * FROM user_activity 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?";
    
    $activities = db_query($sql, [$user_id, $limit]);
    
    return $activities ? $activities : [];
}

/**
 * Record user activity
 * 
 * @param int $user_id User ID
 * @param string $type Activity type
 * @param string $title Activity title
 * @param string $description Activity description
 * @param int|null $drawing_id Related drawing ID (if applicable)
 * @return int|false Activity ID or false on error
 */
function record_activity($user_id, $type, $title, $description = '', $drawing_id = null) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $data = [
        'user_id' => $user_id,
        'type' => $type,
        'title' => $title,
        'description' => $description
    ];
    
    if ($drawing_id) {
        $data['drawing_id'] = $drawing_id;
    }
    
    return $db->insert('user_activity', $data);
}

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool Whether email is valid
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if file extension is allowed
 * 
 * @param string $filename Filename to check
 * @return bool Whether extension is allowed
 */
function is_allowed_extension($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_EXTENSIONS);
}

/**
 * Resize a base64 image
 * 
 * @param string $base64Image Base64 encoded image
 * @param int $maxWidth Maximum width
 * @param int $maxHeight Maximum height
 * @return string|false Resized base64 image or false on error
 */
function resize_base64_image($base64Image, $maxWidth = 800, $maxHeight = 600) {
    // Extract image content
    $image_parts = explode(';base64,', $base64Image);
    
    if (count($image_parts) < 2) {
        return false;
    }
    
    $image_type_aux = explode('image/', $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);
    
    // Create image resource
    $img = @imagecreatefromstring($image_base64);
    if (!$img) {
        return false;
    }
    
    // Get original dimensions
    $width = imagesx($img);
    $height = imagesy($img);
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    
    if ($ratio >= 1) {
        // No need to resize if already smaller
        return $base64Image;
    }
    
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    
    // Create new image
    $newImg = imagecreatetruecolor($newWidth, $newHeight);
    
    // Handle transparency for PNG
    if ($image_type === 'png') {
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize
    imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Output to buffer
    ob_start();
    
    if ($image_type === 'jpeg' || $image_type === 'jpg') {
        imagejpeg($newImg, null, 90);
    } elseif ($image_type === 'png') {
        imagepng($newImg, null, 9);
    } elseif ($image_type === 'gif') {
        imagegif($newImg);
    } else {
        // Unsupported format
        ob_end_clean();
        return false;
    }
    
    $imageData = ob_get_clean();
    
    // Free memory
    imagedestroy($img);
    imagedestroy($newImg);
    
    // Return base64
    return 'data:image/' . $image_type . ';base64,' . base64_encode($imageData);
}

/**
 * Get client IP address
 * 
 * @return string Client IP address
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip;
}

/**
 * Check if user agent is mobile
 * 
 * @return bool Whether user agent is mobile
 */
function is_mobile() {
    $useragent = $_SERVER['HTTP_USER_AGENT'];
    
    return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4));
}

/**
 * Compress and optimize base64 image
 * 
 * @param string $base64Image Base64 encoded image
 * @param int $quality Quality (0-100)
 * @return string Optimized base64 image
 */
function optimize_base64_image($base64Image, $quality = 85) {
    // Extract image content
    $image_parts = explode(';base64,', $base64Image);
    
    if (count($image_parts) < 2) {
        return $base64Image;
    }
    
    $image_type_aux = explode('image/', $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);
    
    // Create image resource
    $img = @imagecreatefromstring($image_base64);
    if (!$img) {
        return $base64Image;
    }
    
    // Output to buffer
    ob_start();
    
    if ($image_type === 'jpeg' || $image_type === 'jpg') {
        imagejpeg($img, null, $quality);
    } elseif ($image_type === 'png') {
        // For PNG, use compression level (0-9)
        imagepng($img, null, min(9, round(9 - ($quality / 10))));
    } elseif ($image_type === 'gif') {
        imagegif($img);
    } else {
        // Unsupported format
        ob_end_clean();
        return $base64Image;
    }
    
    $imageData = ob_get_clean();
    
    // Free memory
    imagedestroy($img);
    
    // Return base64
    return 'data:image/' . $image_type . ';base64,' . base64_encode($imageData);
}

function register_user($username, $email, $password) {
    $db = Database::getInstance(); // 👈 ensure this line exists

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into DB using insert()
    $data = [
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword
    ];

    $userId = $db->insert('users', $data);
    return $userId;
}


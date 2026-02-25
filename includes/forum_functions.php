<?php






function getForumPosts($pdo, $filters = [], $current_user_id = null, $current_user_type = null)
{
    $where = ["1=1"];
    $params = [];

    if (isset($filters['category']) && $filters['category'] !== '') {
        $where[] = "fp.category = ?";
        $params[] = $filters['category'];
    }

    if (isset($filters['status']) && $filters['status'] !== '') {
        $where[] = "fp.status = ?";
        $params[] = $filters['status'];
    }

    if (isset($filters['search']) && $filters['search'] !== '') {
        $where[] = "(fp.title LIKE ? OR fp.content LIKE ? OR fp.tags LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    if (isset($filters['user_id']) && $filters['user_id']) {
        $where[] = "fp.user_id = ? AND fp.user_type = ?";
        $params[] = $filters['user_id'];
        $params[] = $filters['user_type'];
    }

    
    
    
    
    

    $whereClause = implode(' AND ', $where);

    $orderBy = "fp.is_pinned DESC, fp.created_at DESC";
    if (isset($filters['sort'])) {
        switch ($filters['sort']) {
            case 'oldest':
                $orderBy = "fp.is_pinned DESC, fp.created_at ASC";
                break;
            case 'most_viewed':
                $orderBy = "fp.is_pinned DESC, fp.views DESC";
                break;
            case 'most_liked':
                $orderBy = "fp.is_pinned DESC, like_count DESC";
                break;
        }
    }

    
    $userLikedSubquery = "0";
    if ($current_user_id && $current_user_type) {
        $userLikedSubquery = "(SELECT COUNT(*) FROM forum_likes WHERE target_id = fp.id AND target_type = 'post' AND user_id = " . intval($current_user_id) . " AND user_type = '" . addslashes($current_user_type) . "')";
    }

    $sql = "
        SELECT fp.*,
            CASE 
                WHEN fp.user_type = 'patient' THEN CONCAT(p.fname, ' ', p.lname)
                WHEN fp.user_type = 'doctor' THEN d.fullname
                WHEN fp.user_type = 'admin' THEN 'Admin'
            END as author_name,
            CASE 
                WHEN fp.user_type = 'patient' THEN p.email
                WHEN fp.user_type = 'doctor' THEN d.email
                WHEN fp.user_type = 'admin' THEN 'admin@hospital.vn'
            END as author_email,
            (SELECT COUNT(*) FROM forum_likes WHERE target_id = fp.id AND target_type = 'post') as like_count,
            (SELECT COUNT(*) FROM forum_comments WHERE post_id = fp.id) as comment_count,
            $userLikedSubquery as user_liked
        FROM forum_posts fp
        LEFT JOIN patreg p ON (fp.user_id = p.pid AND fp.user_type = 'patient')
        LEFT JOIN doctb d ON (fp.user_id = d.id AND fp.user_type = 'doctor')
        WHERE $whereClause
        ORDER BY $orderBy
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}



function getForumPost($pdo, $post_id)
{
    $sql = "
        SELECT fp.*,
            CASE 
                WHEN fp.user_type = 'patient' THEN CONCAT(p.fname, ' ', p.lname)
                WHEN fp.user_type = 'doctor' THEN d.fullname
                WHEN fp.user_type = 'admin' THEN 'Admin'
            END as author_name,
            CASE 
                WHEN fp.user_type = 'patient' THEN p.email
                WHEN fp.user_type = 'doctor' THEN d.email
                WHEN fp.user_type = 'admin' THEN 'admin@hospital.vn'
            END as author_email,
            (SELECT COUNT(*) FROM forum_likes WHERE target_id = fp.id AND target_type = 'post') as like_count,
            (SELECT COUNT(*) FROM forum_comments WHERE post_id = fp.id) as comment_count
        FROM forum_posts fp
        LEFT JOIN patreg p ON (fp.user_id = p.pid AND fp.user_type = 'patient')
        LEFT JOIN doctb d ON (fp.user_id = d.id AND fp.user_type = 'doctor')
        WHERE fp.id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id]);
    return $stmt->fetch();
}



function createForumPost($pdo, $data)
{
    $sql = "INSERT INTO forum_posts (user_id, user_type, title, content, tags, category, privacy) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['user_id'],
        $data['user_type'],
        $data['title'],
        $data['content'],
        $data['tags'] ?? null,
        $data['category'] ?? 'general',
        $data['privacy'] ?? 'public'
    ]);
}



function getForumComments($pdo, $post_id)
{
    $sql = "
        SELECT fc.*,
            CASE 
                WHEN fc.user_type = 'patient' THEN CONCAT(p.fname, ' ', p.lname)
                WHEN fc.user_type = 'doctor' THEN d.fullname
                WHEN fc.user_type = 'admin' THEN 'Admin'
            END as author_name,
            (SELECT COUNT(*) FROM forum_likes WHERE target_id = fc.id AND target_type = 'comment') as like_count
        FROM forum_comments fc
        LEFT JOIN patreg p ON (fc.user_id = p.pid AND fc.user_type = 'patient')
        LEFT JOIN doctb d ON (fc.user_id = d.id AND fc.user_type = 'doctor')
        WHERE fc.post_id = ?
        ORDER BY COALESCE(fc.parent_id, fc.id), fc.created_at ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id]);
    return $stmt->fetchAll();
}



function addForumComment($pdo, $data)
{
    $sql = "INSERT INTO forum_comments (post_id, user_id, user_type, content, parent_id) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['post_id'],
        $data['user_id'],
        $data['user_type'],
        $data['content'],
        $data['parent_id'] ?? null
    ]);
}



function toggleForumLike($pdo, $user_id, $user_type, $target_id, $target_type)
{
    
    $check = $pdo->prepare("SELECT id FROM forum_likes WHERE user_id = ? AND user_type = ? AND target_id = ? AND target_type = ?");
    $check->execute([$user_id, $user_type, $target_id, $target_type]);

    if ($check->fetch()) {
        
        $stmt = $pdo->prepare("DELETE FROM forum_likes WHERE user_id = ? AND user_type = ? AND target_id = ? AND target_type = ?");
        $stmt->execute([$user_id, $user_type, $target_id, $target_type]);
        return false; 
    } else {
        
        $stmt = $pdo->prepare("INSERT INTO forum_likes (user_id, user_type, target_id, target_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_type, $target_id, $target_type]);
        return true; 
    }
}



function hasForumLiked($pdo, $user_id, $user_type, $target_id, $target_type)
{
    $stmt = $pdo->prepare("SELECT id FROM forum_likes WHERE user_id = ? AND user_type = ? AND target_id = ? AND target_type = ?");
    $stmt->execute([$user_id, $user_type, $target_id, $target_type]);
    return $stmt->fetch() !== false;
}



function incrementPostViews($pdo, $post_id)
{
    $stmt = $pdo->prepare("UPDATE forum_posts SET views = views + 1 WHERE id = ?");
    return $stmt->execute([$post_id]);
}



function getForumAttachments($pdo, $post_id)
{
    $stmt = $pdo->prepare("SELECT * FROM forum_attachments WHERE post_id = ? ORDER BY created_at ASC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll();
}



function addDoctorRating($pdo, $data)
{
    $sql = "INSERT INTO doctor_ratings (doctor_id, patient_id, appointment_id, rating, review, professionalism, communication, environment, wait_time, is_verified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $data['doctor_id'],
        $data['patient_id'],
        $data['appointment_id'] ?? null,
        $data['rating'],
        $data['review'] ?? null,
        $data['professionalism'] ?? null,
        $data['communication'] ?? null,
        $data['environment'] ?? null,
        $data['wait_time'] ?? null,
        $data['is_verified'] ?? 0
    ]);

    
    if ($result) {
        updateDoctorAverageRating($pdo, $data['doctor_id']);
    }

    return $result;
}



function updateDoctorAverageRating($pdo, $doctor_id)
{
    $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM doctor_ratings WHERE doctor_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$doctor_id]);
    $result = $stmt->fetch();

    $updateSql = "UPDATE doctb SET average_rating = ?, total_ratings = ? WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    return $updateStmt->execute([
        round($result['avg_rating'], 2),
        $result['total'],
        $doctor_id
    ]);
}



function getDoctorRatings($pdo, $doctor_id, $limit = null)
{
    $sql = "
        SELECT dr.*, 
            CONCAT(p.fname, ' ', p.lname) as patient_name,
            p.avatar as patient_avatar
        FROM doctor_ratings dr
        JOIN patreg p ON dr.patient_id = p.pid
        WHERE dr.doctor_id = ?
        ORDER BY dr.created_at DESC
    ";

    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$doctor_id]);
    return $stmt->fetchAll();
}



function getDoctorRatingStats($pdo, $doctor_id)
{
    $sql = "
        SELECT 
            AVG(rating) as avg_rating,
            COUNT(*) as total_ratings,
            AVG(professionalism) as avg_professionalism,
            AVG(communication) as avg_communication,
            AVG(environment) as avg_environment,
            AVG(wait_time) as avg_wait_time,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM doctor_ratings
        WHERE doctor_id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$doctor_id]);
    return $stmt->fetch();
}



function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return "Vừa xong";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " phút trước";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " giờ trước";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " ngày trước";
    } else {
        return date('d/m/Y', $timestamp);
    }
}



function h($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

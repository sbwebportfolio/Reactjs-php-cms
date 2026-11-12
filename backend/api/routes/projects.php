<?php
/** Public read-only projects API: GET /api/v1/projects and /projects/{slug}. */
require_once __DIR__ . '/../responses/json.php';
return function (string $method, array $pathParts): void {
    if ($method !== 'GET') apiError('Method not allowed.', 'METHOD_NOT_ALLOWED', 405);
    $conn = dbConnection();
    $slug = $pathParts[1] ?? '';
    if ($slug !== '') {
        $stmt = $conn->prepare("SELECT p.id,p.title,p.slug,p.excerpt,p.content,p.location,p.client_name,p.start_date,p.completion_date,p.project_status,p.featured_image,p.is_featured,p.published_at,c.name AS category_name FROM projects p LEFT JOIN project_categories c ON c.id=p.category_id WHERE p.slug=? AND p.status='published' AND (p.published_at IS NULL OR p.published_at<=NOW()) LIMIT 1");
        $stmt->bind_param('s', $slug); $stmt->execute(); $project = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$project) apiError('Project not found.', 'NOT_FOUND', 404);
        apiSuccess(['project'=>$project], 'Project loaded.');
    }
    $page = max(1, (int)($_GET['page'] ?? 1)); $limit = min(50, max(1,(int)($_GET['per_page'] ?? 12))); $offset = ($page-1)*$limit;
    $search = trim((string)($_GET['search'] ?? '')); $category = filter_var($_GET['category'] ?? null,FILTER_VALIDATE_INT);
    $conditions = ["p.status='published'", '(p.published_at IS NULL OR p.published_at<=NOW())']; $params=[]; $types='';
    if ($search !== '') { $conditions[]='(p.title LIKE ? OR p.excerpt LIKE ? OR p.location LIKE ?)'; $term='%'.$search.'%'; array_push($params,$term,$term,$term); $types.='sss'; }
    if ($category) { $conditions[]='p.category_id=?'; $params[]=(int)$category; $types.='i'; }
    $where=implode(' AND ',$conditions);
    $sql="SELECT p.id,p.title,p.slug,p.excerpt,p.location,p.client_name,p.featured_image,p.is_featured,p.published_at,c.name AS category_name FROM projects p LEFT JOIN project_categories c ON c.id=p.category_id WHERE {$where} ORDER BY p.is_featured DESC,p.published_at DESC,p.id DESC LIMIT ? OFFSET ?";
    $stmt=$conn->prepare($sql); $params[]=$limit; $params[]=$offset; $types.='ii'; $stmt->bind_param($types,...$params); $stmt->execute(); $items=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    $countSql="SELECT COUNT(*) AS total FROM projects p WHERE {$where}"; $count=$conn->prepare($countSql); $countParams=array_slice($params,0,-2); $countTypes=substr($types,0,-2); if($countParams)$count->bind_param($countTypes,...$countParams); $count->execute(); $total=(int)$count->get_result()->fetch_assoc()['total']; $count->close();
    apiSuccess(['projects'=>$items,'pagination'=>['page'=>$page,'per_page'=>$limit,'total'=>$total,'pages'=>(int)ceil($total/$limit)]], 'Projects loaded.');
};

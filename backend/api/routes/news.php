<?php
/** Public read-only news API: GET /api/v1/news and /api/v1/news/{slug}. */
require_once __DIR__ . '/../responses/json.php';
return function (string $method, array $pathParts): void {
    if ($method !== 'GET') apiError('Method not allowed.', 'METHOD_NOT_ALLOWED', 405);
    $conn=dbConnection(); $slug=$pathParts[1]??'';
    if($slug!==''){
        $stmt=$conn->prepare("SELECT n.id,n.title,n.slug,n.excerpt,n.content,n.featured_image,n.is_featured,n.published_at,c.name AS category_name FROM news n LEFT JOIN news_categories c ON c.id=n.category_id WHERE n.slug=? AND n.status='published' AND n.published_at<=NOW() LIMIT 1");
        $stmt->bind_param('s',$slug);$stmt->execute();$article=$stmt->get_result()->fetch_assoc();$stmt->close();
        if(!$article)apiError('News article not found.','NOT_FOUND',404);
        apiSuccess(['news'=>$article],'News article loaded.');
    }
    $page=max(1,(int)($_GET['page']??1));$limit=min(50,max(1,(int)($_GET['per_page']??12)));$offset=($page-1)*$limit;
    $search=trim((string)($_GET['search']??''));$category=filter_var($_GET['category']??null,FILTER_VALIDATE_INT);
    $conditions=["n.status='published'",'n.published_at<=NOW()'];$params=[];$types='';
    if($search!==''){$conditions[]='(n.title LIKE ? OR n.excerpt LIKE ?)';$term='%'.$search.'%';$params[]=$term;$params[]=$term;$types.='ss';}
    if($category){$conditions[]='n.category_id=?';$params[]=(int)$category;$types.='i';}
    $where=implode(' AND ',$conditions);
    $stmt=$conn->prepare("SELECT n.id,n.title,n.slug,n.excerpt,n.featured_image,n.is_featured,n.published_at,c.name AS category_name FROM news n LEFT JOIN news_categories c ON c.id=n.category_id WHERE {$where} ORDER BY n.is_featured DESC,n.published_at DESC,n.id DESC LIMIT ? OFFSET ?");
    $listParams=$params;$listParams[]=$limit;$listParams[]=$offset;$stmtTypes=$types.'ii';$stmt->bind_param($stmtTypes,...$listParams);$stmt->execute();$items=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
    $count=$conn->prepare("SELECT COUNT(*) AS total FROM news n WHERE {$where}");if($params)$count->bind_param($types,...$params);$count->execute();$total=(int)$count->get_result()->fetch_assoc()['total'];$count->close();
    apiSuccess(['news'=>$items,'pagination'=>['page'=>$page,'per_page'=>$limit,'total'=>$total,'pages'=>(int)ceil($total/$limit)]],'News loaded.');
};

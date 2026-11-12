<?php
/** Public blog read API: GET /api/v1/blog and /api/v1/blog/{slug}. */
require_once __DIR__ . '/../responses/json.php';
return function(string $method,array $pathParts):void{
 if($method!=='GET')apiError('Method not allowed.','METHOD_NOT_ALLOWED',405);
 $conn=dbConnection();$slug=$pathParts[1]??'';
 if($slug!==''){$stmt=$conn->prepare("SELECT b.id,b.title,b.slug,b.excerpt,b.content,b.featured_image,b.published_at,c.name category_name FROM blog_posts b LEFT JOIN blog_categories c ON c.id=b.category_id WHERE b.slug=? AND b.status='published' AND b.published_at<=NOW() LIMIT 1");$stmt->bind_param('s',$slug);$stmt->execute();$post=$stmt->get_result()->fetch_assoc();$stmt->close();if(!$post)apiError('Blog post not found.','NOT_FOUND',404);apiSuccess(['post'=>$post],'Blog post loaded.');}
 $page=max(1,(int)($_GET['page']??1));$limit=min(50,max(1,(int)($_GET['per_page']??12)));$offset=($page-1)*$limit;$search=trim((string)($_GET['search']??''));$category=filter_var($_GET['category']??null,FILTER_VALIDATE_INT);$where=["b.status='published'",'b.published_at<=NOW()'];$params=[];$types='';
 if($search!==''){$where[]='(b.title LIKE ? OR b.excerpt LIKE ?)';$term='%'.$search.'%';$params[]=$term;$params[]=$term;$types.='ss';}if($category){$where[]='b.category_id=?';$params[]=(int)$category;$types.='i';}$whereSql=implode(' AND ',$where);
 $stmt=$conn->prepare("SELECT b.id,b.title,b.slug,b.excerpt,b.featured_image,b.published_at,c.name category_name FROM blog_posts b LEFT JOIN blog_categories c ON c.id=b.category_id WHERE {$whereSql} ORDER BY b.published_at DESC,b.id DESC LIMIT ? OFFSET ?");$listParams=$params;$listParams[]=$limit;$listParams[]=$offset;$stmt->bind_param($types.'ii',...$listParams);$stmt->execute();$posts=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
 $count=$conn->prepare("SELECT COUNT(*) total FROM blog_posts b WHERE {$whereSql}");if($params)$count->bind_param($types,...$params);$count->execute();$total=(int)$count->get_result()->fetch_assoc()['total'];$count->close();apiSuccess(['posts'=>$posts,'pagination'=>['page'=>$page,'per_page'=>$limit,'total'=>$total,'pages'=>(int)ceil($total/$limit)]],'Blog posts loaded.');
};

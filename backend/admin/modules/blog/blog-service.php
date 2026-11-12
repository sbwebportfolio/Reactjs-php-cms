<?php
/** Blog persistence helpers. */
require_once __DIR__ . '/../../../includes/functions.php';

function blogSlug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function blogLoad(int $id): ?array
{
    $stmt = dbConnection()->prepare('SELECT id,category_id,title,slug,excerpt,content,featured_image,status,published_at FROM blog_posts WHERE id=? LIMIT 1');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $row=$stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function blogSave(array $input, ?int $id, int $adminId): array
{
    $title=sanitizeString($input['title']??'',240);
    $slug=blogSlug((string)($input['slug']??$title));
    $excerpt=trim((string)($input['excerpt']??''));
    $content=trim((string)($input['content']??''));
    $image=trim((string)($input['featured_image']??''));
    $status=(string)($input['status']??'draft');
    $category=filter_var($input['category_id']??null,FILTER_VALIDATE_INT);
    $category=$category && $category>0 ? $category : null;
    $dateInput=trim((string)($input['published_at']??''));
    $publishedAt=null;
    $errors=[];
    if($title==='')$errors['title']='Title is required.';
    if($slug==='')$errors['slug']='A valid slug is required.';
    if($content==='')$errors['content']='Post content is required.';
    if(!in_array($status,['draft','published','scheduled','archived'],true))$errors['status']='Choose a valid status.';
    if(mb_strlen($excerpt)>5000)$errors['excerpt']='Excerpt must be 5,000 characters or fewer.';
    if(mb_strlen($content)>1000000)$errors['content']='Post content is too long.';
    if(mb_strlen($image)>500)$errors['featured_image']='Image reference is too long.';
    if($dateInput!==''){
        $date=DateTime::createFromFormat('Y-m-d\\TH:i',$dateInput);
        if(!$date)$errors['published_at']='Publication date is invalid.';
        else $publishedAt=$date->format('Y-m-d H:i:s');
    }elseif($status==='published'){$publishedAt=date('Y-m-d H:i:s');}
    elseif($status==='scheduled')$errors['published_at']='A publication date is required for scheduled posts.';
    if($errors)return $errors;

    $conn=dbConnection();
    if($id===null){
        $stmt=$conn->prepare('INSERT INTO blog_posts(category_id,title,slug,excerpt,content,featured_image,status,published_at,author_id) VALUES(?,?,?,?,?,?,?,?,?)');
        $stmt->bind_param('isssssssi',$category,$title,$slug,$excerpt,$content,$image,$status,$publishedAt,$adminId);
    }else{
        $stmt=$conn->prepare('UPDATE blog_posts SET category_id=?,title=?,slug=?,excerpt=?,content=?,featured_image=?,status=?,published_at=?,author_id=? WHERE id=?');
        $stmt->bind_param('issssss sii',$category,$title,$slug,$excerpt,$content,$image,$status,$publishedAt,$adminId,$id);
    }
    try{$stmt->execute();}catch(mysqli_sql_exception $e){$stmt->close();if((int)$e->getCode()===1062)return ['slug'=>'This blog slug is already in use.'];throw $e;}
    $stmt->close();
    return [];
}

<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../lib/helpers.php';
if(APP_ENV!=='local'){fwrite(STDERR,"Blog demo seeding is local-only.\n");exit(1);}
$db=get_db();
$admin=$db->query('SELECT id FROM admin_users WHERE status="active" ORDER BY created_at LIMIT 1')->fetchColumn();
if(!$admin){fwrite(STDERR,"No active administrator found. Run seed-admin-demo.php first.\n");exit(1);}
$posts=[
 ['the-hands-behind-indias-living-crafts','The hands behind India’s living crafts','Craft & Culture','craft','/conscious/assets/images/about/artisan-weaving-natural-basket.jpg','An Indian artisan weaving a natural fibre basket','A closer look at why handmade knowledge lives in people—not production lines.','Conscious India Editorial',6,[
  ['type'=>'paragraph','text'=>'A handmade object carries more than material. It carries the memory of a place, the rhythm of a practiced hand and choices refined across generations.'],
  ['type'=>'heading','text'=>'Craft is knowledge you can hold'],
  ['type'=>'paragraph','text'=>'Indian craft traditions are living systems. Materials come from local landscapes, tools are adapted by families, and techniques move from one generation to the next through observation and repetition.'],
  ['type'=>'quote','text'=>'When we know who made an object, value moves back toward skill, patience and dignity.','cite'=>'Conscious India'],
  ['type'=>'heading','text'=>'What thoughtful buying protects'],
  ['type'=>'list','items'=>['Fair value for makers and artisan collectives','Regional materials and low-impact techniques','Cultural knowledge that cannot be recreated by a factory','Objects designed to be kept, repaired and remembered']],
  ['type'=>'paragraph','text'=>'The future of craft depends on making this value visible. Better stories help customers understand not only what they are buying, but what their choice helps carry forward.']]],
 ['natural-materials-for-a-slower-home','Natural materials for a slower, calmer home','Conscious Living','living','/conscious/assets/images/portfolio-slow-home.png','A calm Indian home styled with natural materials','Simple ways to build a home that feels grounded, useful and made to last.','Pooja G J',5,[
  ['type'=>'paragraph','text'=>'A conscious home does not need to be perfectly styled. It needs objects that serve daily life well, age honestly and create a sense of ease.'],
  ['type'=>'heading','text'=>'Begin with how a room feels'],['type'=>'paragraph','text'=>'Natural fibres, warm wood, clay and hand-finished surfaces soften a space because they keep the small variations that mass production removes.'],
  ['type'=>'list','items'=>['Choose fewer pieces with a clear purpose','Prefer repairable materials over sealed composites','Mix handloom textiles with objects already in your home','Leave breathing room rather than filling every surface']],
  ['type'=>'quote','text'=>'A slower home is not about buying a look. It is about making room for what you genuinely use and love.','cite'=>'Conscious India Editorial']]],
 ['why-maker-stories-matter','Why maker stories matter more than labels','Makers','makers','/conscious/assets/images/portfolio-artisan-stories.png','An Indian maker working with traditional craft materials','Certification matters—but real trust also comes from people, process and proof.','Preeti Gaikwad',7,[
  ['type'=>'paragraph','text'=>'A label can summarize a standard, but it cannot show the full journey of an object. Maker stories give context to materials, methods, communities and the value returned to the people doing the work.'],
  ['type'=>'heading','text'=>'From anonymous supply to visible people'],['type'=>'paragraph','text'=>'When customers can see the maker, origin and process, sustainability becomes specific. It can be questioned, compared and improved.'],
  ['type'=>'quote','text'=>'Transparency is not a marketing paragraph. It is the ability to connect a claim with a person, place or verifiable process.','cite'=>'The 11 Ethics of Conscious India'],
  ['type'=>'heading','text'=>'The questions worth asking'],['type'=>'list','items'=>['Who made this and where?','Which materials were used and why?','How is the maker or collective paid?','Can the product be repaired, reused or responsibly returned?']]]],
 ['a-practical-guide-to-low-waste-gifting','A practical guide to low-waste gifting','Sustainability','sustainability','/conscious/assets/images/eco-scene-2.png','Naturally styled eco-friendly gifts for the home','Choose gifts with less packaging, more purpose and a story worth sharing.','Conscious India Editorial',4,[
  ['type'=>'paragraph','text'=>'Good gifting is less about volume and more about attention. A useful, beautifully made object can feel generous without creating unnecessary waste.'],
  ['type'=>'heading','text'=>'A simple gifting checklist'],['type'=>'list','items'=>['Choose useful products suited to the recipient','Look for reusable, recyclable or minimal packaging','Prefer local makers and small-batch production','Include the maker and material story with the gift']],
  ['type'=>'paragraph','text'=>'For corporate gifting, fewer considered options often create a stronger experience than large catalogues of generic merchandise.']]],
 ['how-to-read-a-conscious-product-page','How to read a conscious product page','Sustainability','sustainability','/conscious/assets/images/about/eco-friendly-indian-crafts.jpg','Eco-friendly Indian craft objects made with natural materials','A five-minute framework for looking beyond green claims before you buy.','Conscious India Editorial',5,[
  ['type'=>'paragraph','text'=>'Words such as natural, green and sustainable can mean very different things. A strong product page should give enough detail for you to judge the claim.'],
  ['type'=>'heading','text'=>'Look for evidence, not adjectives'],['type'=>'list','items'=>['Named materials rather than vague eco-friendly descriptions','A clear place of origin and maker or vendor identity','Care instructions that help the object last','Packaging and end-of-life information','Relevant certifications with issuer and validity details']],
  ['type'=>'quote','text'=>'The clearest sustainability claim is one that a customer can understand and verify.','cite'=>'Conscious India Editorial'],
  ['type'=>'paragraph','text'=>'Conscious buying is not a search for perfection. It is a practice of asking better questions and choosing the more responsible option available.']]],
];
$find=$db->prepare('SELECT id FROM cms_entries WHERE entry_type="blog" AND slug=:slug LIMIT 1');
$insert=$db->prepare('INSERT INTO cms_entries (id,entry_type,slug,title,content_json,status,created_by,created_at,updated_at,published_at) VALUES (:id,"blog",:slug,:title,:content,"published",:admin,UTC_TIMESTAMP(),UTC_TIMESTAMP(),:published)');
$update=$db->prepare('UPDATE cms_entries SET title=:title,content_json=:content,status="published",updated_at=UTC_TIMESTAMP(),published_at=COALESCE(published_at,:published) WHERE id=:id');
$created=0;$updated=0;
foreach($posts as $index=>$post){[$slug,$title,$category,$categoryKey,$image,$alt,$excerpt,$author,$minutes,$body]=$post;$payload=json_encode(['category'=>$category,'category_key'=>$categoryKey,'hero_image'=>$image,'hero_alt'=>$alt,'image_caption'=>'Stories, materials and makers from Conscious India.','excerpt'=>$excerpt,'author'=>$author,'reading_minutes'=>$minutes,'body'=>$body],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);$published=gmdate('Y-m-d H:i:s',time()-$index*86400*7);$find->execute(['slug'=>$slug]);$id=$find->fetchColumn();if($id){$update->execute(['id'=>$id,'title'=>$title,'content'=>$payload,'published'=>$published]);$updated++;}else{$insert->execute(['id'=>uuid_string(),'slug'=>$slug,'title'=>$title,'content'=>$payload,'admin'=>$admin,'published'=>$published]);$created++;}}
fwrite(STDOUT,"Local CMS blogs: {$created} created, {$updated} updated.\n");

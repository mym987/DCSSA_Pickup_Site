<?php
getCode(5,100,40); 
function getCode($num,$w,$h) { 
    $str = "1,2,3,4,5,6,7,8,9,A,B,C,E,F,G,H,J,K,L,M,N,P,Q,R,S,T,V,W,X,Y";      //要显示的字符，可自己进行增删
    $list = explode(",", $str);
    $cmax = count($list) - 1;
    $code = '';
    for ( $i=0; $i < 5; $i++ ){
      $randnum = mt_rand(0, $cmax);
      $code .= $list[$randnum];           //取出字符，组合成为我们要的验证码字符
    }
    $_SESSION['code'] = $code;        //将字符放入SESSION中 
    //创建图片，定义颜色值 
    header("Content-type: image/PNG"); 
    $im = imagecreate($w, $h); 

    $black = imagecolorallocate($im, 0,0,0);     //此条及以下三条为设置的颜色
    $white = imagecolorallocate($im, 255,255,255);
    $gray = imagecolorallocate($im, 200,200,200);
    $red = imagecolorallocate($im, 255, 0, 0);
    imagefill($im,0,0,$white);     //给图片填充颜色
    //填充背景 
    imagefill($im, 0, 0, $white); 
 
    //画边框 
    imagerectangle($im, 0, 0, $w-1, $h-1, $black); 
 
    //随机绘制两条虚线，起干扰作用 
    $style = array ($black,$black,$black,$black,$black, 
        $gray,$gray,$gray,$gray,$gray 
    ); 
    imagesetstyle($im, $style); 
    $y1 = rand(0, $h); 
    $y2 = rand(0, $h); 
    $y3 = rand(0, $h); 
    $y4 = rand(0, $h); 
    imageline($im, 0, $y1, $w, $y3, IMG_COLOR_STYLED); 
    imageline($im, 0, $y2, $w, $y4, IMG_COLOR_STYLED); 
 
    //在画布上随机生成大量黑点，起干扰作用; 
    for ($i = 0; $i < 20; $i++) { 
        imagesetpixel($im, rand(0, $w), rand(0, $h), $black); 
        imagearc($im, rand(0, $w), rand(0, $h), 20, 20, 75, 170, $gray);    //加入弧线状干扰素
        imageline($im, rand(0, $w),rand(0, $w), rand(0, $h), rand(0, $h), $red);    //加入线条状干扰素
    } 
    //将数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成 
    $strx = rand(10, 20); 
    for ($i = 0; $i < $num; $i++) { 
        $strpos = rand(1, 20); 
        imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black); 
        $strx += rand(10, 20); 
    } 
    imagepng($im);//输出图片 
    imagedestroy($im);//释放图片所占内存 
} 
?>
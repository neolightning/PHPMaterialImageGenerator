<?php
 function overflow32($v)
    {
        $v = $v % 4294967296;
        if ($v > 2147483647) return $v - 4294967296;
        elseif ($v < -2147483648) return $v + 4294967296;
        else return $v;
    }

    function hashCode($s)
    {
        $h = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++)
        {
            $h = overflow32(31 * $h + ord($s[$i]));
        }

        return $h;
    }

    if (isset($_GET['min_shapes']))
        $minShapes = $_GET['min_shapes'];
    else
        $minShapes = 3;

    if (isset($_GET['max_shapes']))
        $maxShapes = $_GET['max_shapes'];
    else
        $maxShapes = 10;

    if ($minShapes > $maxShapes)
        $minShapes = $maxShapes;

    if (isset($_GET['width']))
        $width = $_GET['width'];
    else
        $width = 500;

    if (isset($_GET['height']))
        $height = $_GET['height'];
    else
        $height = 214;

    if (isset($_GET['min_size']))
        $minSize = $_GET['min_size'];
    else
        $minSize = 10;

    if (isset($_GET['max_size']))
        $maxSize = $_GET['max_size'];
    else
        $maxSize = $width;

    if (isset($_GET['seed']))
        $seed = hashCode($_GET['seed']);
    else
        $seed = round(microtime(true) * 1000);

    mt_srand($seed);

    function getRandomColor($img)
    {
        $r = mt_rand(0, 255);
        $g = mt_rand(0, 255);
        $b = mt_rand(0, 255);
        return ImageColorAllocate($img, $r, $g, $b);
    }

    function getShapeCount()
    {
        global $minShapes;
        global $maxShapes;
        return mt_rand($minShapes, $maxShapes);
    }

    function drawRandomShape($img, $color, $angle = -100)
    {
        global $width;
        global $height;
        global $minSize;
        global $maxSize;
        if ($angle == -100)
            $angle = mt_rand(0, 359);
        $type = mt_rand(0, 2);
        $posX = mt_rand(0, $width);
        $posY = mt_rand(0, $height);
        $w = mt_rand($minSize, $maxSize);
        $h = mt_rand($minSize, $maxSize);
        switch ($type)
        {
            case 0:
                drawFilledRectangle($img, $posX, $posY, $w, $h, $angle, $color);
                break;
            case 1:
                ImageFilledEllipse($img, $posX, $posY, $w, $w, $color);
                break;
            case 2:
                drawFilledPentagon($img, $posX, $posY, $w, $w, $angle, $color);
                break;
        }
    }

    function rotatePoint($x, $y, $px, $py, $angle)
    {
        // translate point to origin
        $tx = $x - $px;
        $ty = $y - $py;

        // now apply rotation
        $rx = $tx * cos($angle) - $ty * sin($angle);
        $ry = $tx * sin($angle) + $ty * cos($angle);

        $nx = $rx + $px;
        $ny = $ry + $py;

        return array($nx, $ny);
    }

    function drawFilledPentagon($img, $x, $y, $w, $h, $angle, $color)
    {
        $angled = deg2rad($angle);
        $px = $x + ($w / 2);
        $py = $y + ($h / 2);

        list($x1, $y1) = rotatePoint($x, $y, $px, $py, $angled);
        list($x2, $y2) = rotatePoint($x, $y + $h, $px, $py, $angled);
        list($x3, $y3) = rotatePoint($x + $w, $y + $h, $px, $py, $angled);
        list($x4, $y4) = rotatePoint($x + $w, $y, $px, $py, $angled);
        list($x4, $y4) = rotatePoint($x + $y, $w + $h, $px, $py, $angled);

        $points = array(
            $x1, $y1,
            $x2, $y2,
            $x3, $y3,
            $x4, $y4
        );
        ImageFilledPolygon($img, $points, count($points) / 2, $color);
    }

    function drawFilledRectangle($img, $x, $y, $w, $h, $angle, $color)
    {
        $angled = deg2rad($angle);
        $px = $x + ($w / 2);
        $py = $y + ($h / 2);

        list($x1, $y1) = rotatePoint($x, $y, $px, $py, $angled);
        list($x2, $y2) = rotatePoint($x, $y + $h, $px, $py, $angled);
        list($x3, $y3) = rotatePoint($x + $w, $y + $h, $px, $py, $angled);
        list($x4, $y4) = rotatePoint($x + $w, $y, $px, $py, $angled);

        $points = array(
            $x1, $y1,
            $x2, $y2,
            $x3, $y3,
            $x4, $y4
        );
        ImageFilledPolygon($img, $points, count($points) / 2, $color);
    }

    $handle = ImageCreate($width, $height) or die("Cannot Create image");
    $bgColor = getRandomColor($handle);

    for ($i = 0; $i < getShapeCount(); $i++) {
        $color = getRandomColor($handle);
        drawRandomShape($handle, $color);
    }

    header ("Content-type: image/png");
    ImagePng($handle);
?>

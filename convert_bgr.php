<?php

    $img = imagecreatefrompng('./graphics/bridge.png');
    $width = imagesx($img);
    $height = imagesy($img);
    echo "Image: $width x $height\n";
    $bytes_dx = intval($width / 8);
    echo "Bytes original: ".($bytes_dx*3*$height)."\n";
    
    // tiles array
    $bgrArray = Array();
    
    // scan image and create array
    for ($y=0; $y<$height; $y++)
    {
        for ($bytex=0; $bytex<$bytes_dx; $bytex++)
        {
            $res = 0; 
            for ($x=0; $x<8; $x++)
            {
                $py = $y;
	            $px = $bytex*8 + $x;
	            $res = ($res >> 1) & 0x00FFFFFF;
                $rgb_index = imagecolorat($img, $px, $py);
                $rgba = imagecolorsforindex($img, $rgb_index);
                $r = $rgba['red'];
                $g = $rgba['green'];
                $b = $rgba['blue'];
                if ($r > 127) { $res = $res | 0x00800000; }
                if ($g > 127) { $res = $res | 0x00008000; }
                if ($b > 127) { $res = $res | 0x00000080; }
            }
            array_push($bgrArray, $res);
        }
    }
    
    ////////////////////////////////////////////////////////////////////////////
    
    echo "Writing CPU uncompressed BGR data ...\n";
    $f = fopen ("_cpu_bgr.dat", "w");
    for ($i=0; $i<count($bgrArray); $i++)
    {
        $rg = ($bgrArray[$i] &0xFFFF00) >> 8;
        $r = ($rg>>8) & 0xFF;
        $g = $rg & 0xFF;
        fwrite($f, chr($g), 1);
        fwrite($f, chr($r), 1);
    }
    fclose($f);

    echo "Writing PPU uncompressed BGR data ...\n";
    $f = fopen ("_ppu_bgr.dat", "w");
    for ($i=0; $i<count($bgrArray); $i++)
    {
        $b = $bgrArray[$i] &0xFF;
        fwrite($f, chr($b), 1);
    }
    fclose($f);

    // packing
    exec("..\packers\lzsa3.exe _cpu_bgr.dat _cpu_bgr_lz.dat");
    exec("..\packers\lzsa3.exe _ppu_bgr.dat _ppu_bgr_lz.dat");
    
function WriteMacFile ( $in_fname, $out_fname, $name )
{
    $f = fopen($in_fname, "rb");
    $g = fopen($out_fname, "w");
    $n = 0;
    fputs($g, "$name:");
    while (!feof($f)) {
        $b = ord(fread($f, 1));
        if ($n == 0) fputs($g, "\t.byte\t");
        fputs($g, decoct($b));
        $n++; if ($n < 16) fputs ($g, ", "); else { $n=0; fputs($g, "\n"); }
    }
    if ($n != 0) fputs($g, "0\n");
    fputs($g, "\n\t.even\n");
    fclose($f);
    fclose($g);
}

    // write packed data to .mac files
    WriteMacFile("_cpu_bgr_lz.dat", "inc_cpu_bgr.mac", "CpuBgr");
    WriteMacFile("_ppu_bgr_lz.dat", "inc_ppu_bgr.mac", "PpuBgr");

?>
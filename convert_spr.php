<?php

    $img = imagecreatefrompng('./graphics/TTiles.png');
    $width = imagesx($img);
    $height = imagesy($img);
    echo "Image: $width x $height\n";
    $tiles_dx = intval($width / 17);
    $tiles_dy = intval($height / 17);
    echo "Tiles: $tiles_dx x $tiles_dy\n";
    
    // tiles array
    $tilesArray = Array();
    
    // scan image and create array
    for ($tiley=0; $tiley<$tiles_dy; $tiley++)
    {
        for ($tilex=0; $tilex<$tiles_dx; $tilex++)
        {
	        $tile = Array();
	        for ($y=0; $y<32; $y++)
            {
                $res = 0; 
		        for ($x=0; $x<8; $x++)
                {
                    $py = $tiley*17 + 1 + ($y>>1);
		            $px = $tilex*17 + 1 + $x + (($y&1)<<3);
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
                array_push($tile, $res);
            }
	        $found = array_push($tilesArray, $tile) - 1;
        }
    }
    
    echo "Different tiles count: ".count($tilesArray)."\n";
    
    ////////////////////////////////////////////////////////////////////////////
    
    echo "Writing CPU tiles data ...\n";
    $f = fopen ("inc_cpu_sprites.mac", "w");
    fputs($f, "TilesCpuData:\n");
    for ($t=0; $t<count($tilesArray); $t++)
    {
	    $tile = $tilesArray[$t];
        $n = 0;
	    for ($i=0; $i<32; $i++)
	    {
	        if ($n==0) fputs($f, "\t.word\t");
	        $rg = ($tile[$i] &0xFFFF00) >> 8;
	        fputs($f, decoct($rg));
	        $n++; if ($n<8) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
	    }
        fputs($f, "\n");
    }
    fputs($f, "\n");
    fclose($f);
    
    ////////////////////////////////////////////////////////////////////////////
    
    echo "Writing PPU tiles data ...\n";
    $f = fopen ("inc_ppu_sprites.mac", "w");
    fputs($f, "TilesPpuData:\n");
    $n=0;
    for ($t=0; $t<count($tilesArray); $t++)
    {
	    $tile = $tilesArray[$t];
    	for ($i=0; $i<16; $i++)
	    {
    	    if ($n==0) fputs($f, "\t.word\t");
	        $bb = ($tile[$i*2] & 0xFF) | (($tile[$i*2+1] & 0xFF) << 8);
	        fputs($f, decoct($bb));
	        $n++; if ($n<16) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
        }
    }
    fputs($f, "\n");
    fclose($f);

?>
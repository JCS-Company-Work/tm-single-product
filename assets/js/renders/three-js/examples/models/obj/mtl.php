<?php
header('Content-type:text; charset=UTF-8');
$fileVersion = $_GET['fileversion'];
$itemColour = $_GET['colour'];
$itemSecondColour = $_GET['secondcolour'];
$itemSecondColourName = $_GET['secondcolourname'];
$itemThirdColour = $_GET['thirdcolour'];
$itemFourthColour = $_GET['fourthcolour'];
$itemUnderColour = $_GET['undercolour'];
$itemGlassColour = $_GET['glasscolour'];
$itemMetalColour = $_GET['metalcolour'];
$itemProfileColour = $_GET['profilecolour'];
$itemMeshColour = $_GET['meshcolour'];
$itemAdjustment = $_GET['adjustment'];


$polished = array("swatch-golden-ambra", "swatch-ivory-gioiello", "swatch-laguna-blanca", "swatch-riverbed", "swatch-calacatta-viola");
$honed = array("swatch-calacatta-macchia-vecchia", "swatch-dark-marquina", "swatch-fior-di-bosco", "swatch-macchia-vecchia", "swatch-quarzite-taj-mahal", "swatch-travertine-silver", "swatch-travertine-silver-horizontal","swatch-travertine-chiaro-horizontal","swatch-travertine-chiaro","swatch-viola-rosso","swatch-taj-mahal-pearl","swatch-statuario","swatch-michelangelo-bianco","swatch-laurent-golden","swatch-calacatta-luxury","swatch-royal-botticino","swatch-mogao-white","swatch-black-horse","swatch-bianca-luna","swatch-arabescato-new","swatch-raffaello");
$realwood = array("swatch-american-walnut", "swatch-black-grey");
$woodeffect = array("swatch-acacia", "swatch-maple", "swatch-moro", "swatch-acacia-horizontal", "swatch-maple-horizontal", "swatch-moro-horizontal", "swatch-mogano", "swatch-mulberry", "swatch-cobolo", "swatch-mogano-horizontal", "swatch-mulberry-horizontal", "swatch-cobolo-horizontal");
$dullmetal = array("banding-dec-bronze", "banding-black");
$fullVein = array("swatch-black-horse", "swatch-arabescato-new");
$yellowProfile = array("swatch-travertino-romano", "swatch-ivory-gioiello", "swatch-royal-botticino", "swatch-sassi-di-matera", "swatch-tundra");
$warmProfile = array("swatch-travertino-minimal-white", "swatch-golden-ambra", "swatch-bianca-luna", "swatch-travertine-chiaro");
$darkProfile = array("swatch-pietra-grey", "swatch-dark-marquina", "swatch-laurent-golden");
?>
# 3ds Max Wavefront OBJ Exporter v0.97b - (c)2007 guruware
# File Created: 17.01.2019 16:55:56


<?php if (!empty($itemColour)) { 

    if (in_array($itemColour, $polished)) {  // if top is Polished  ?>

    newmtl <?php echo $itemColour;?> 
    Ka 1 1 1
    Kd 1 1 1
    Ks 1 1 1
    Ns 1000
    sharpness 1000
    illum 0
    map_Kd textures/<?php echo $itemColour; ?>.jpg<?php echo $fileVersion;

 } elseif (in_array($itemColour, $honed)) {  // if top is Honed  ?>
 
    newmtl <?php echo $itemColour;?> 
    Ka 1 1 1
    Kd 1 1 1
    Ks 1 1 1
    Ns 100
    sharpness 0
    illum 0
    map_Kd textures/<?php echo $itemColour; ?>.jpg<?php echo $fileVersion;
 
 
  } else { // Natural  ?>
 
    newmtl <?php echo $itemColour;?> 
    Ka 1 1 1
    Kd 1 1 1
    Ks 1 1 1
    Ns 50
    sharpness 0
    illum 0
    map_Kd textures/<?php echo $itemColour; ?>.jpg<?php echo $fileVersion;
 
 
  } // End if $itemColour is Natural
}  // End if $itemColour 

?>
	
<?php if (!empty($itemSecondColour)) { 

    if (in_array($itemSecondColour, $polished)) {  // if top is Polished  ?>

    newmtl <?php echo $itemSecondColourName;?> 
    Ka 0.75 0.75 0.75
    Kd 0.75 0.75 0.75
    Ks 0.25 0.25 0.25
    Ns 400
    sharpness 1000
    illum 0
    map_Kd textures/<?php echo $itemSecondColour; ?>.jpg<?php echo $fileVersion;

 } elseif (in_array($itemSecondColour, $honed)) {  // if top is Honed  ?>
 
    newmtl <?php echo $itemSecondColourName;?> 
    Ka 0.6 0.6 0.6
    Kd 0.7 0.7 0.7
    Ks 0.24 0.24 0.24
    Ns 25
    sharpness 0
    illum 0
    map_Kd textures/<?php echo $itemSecondColour; ?>.jpg<?php echo $fileVersion;
 
 
  } elseif (in_array($itemSecondColour, $woodeffect)) {  // if top is Honed  ?>
 
    newmtl <?php echo $itemSecondColourName;?> 
    Ka 0.5 0.5 0.5
    Kd 0.5 0.5 0.5
    Ks 0.25 0.25 0.25
    Ns 15
    sharpness 0
    illum 0
    map_Kd textures/<?php echo $itemSecondColour; ?>.jpg<?php echo $fileVersion;
 
 
  } elseif (in_array($itemSecondColour, $realwood)) {  // if base is wood  ?>
    
    newmtl <?php echo $itemSecondColourName;?>  
    Ka 0.1 0.1 0.1
    Kd 0.640000 0.640000 0.60000
    Ks 0.15 0.15 0.15
    Ns 15
    Ni 1.000000
    d 1.000000
    illum 0
	map_Kd textures/<?php echo $itemSecondColour; ?>.jpg<?php echo $fileVersion;
        
    
  
  } else { // Natural  ?>
 
    newmtl <?php echo $itemSecondColourName;?> 
    Ka 0.85 0.85 0.85
    Kd 0.75 0.75 0.75
    Ks 0.5 0.5 0.5
    Ns 2
    Ni 0.5
    illum 0
    sharpness 0
    map_Kd textures/<?php echo $itemSecondColour; ?>.jpg<?php echo $fileVersion;
 
 
  } // End if $itemSecondColour is Natural
}  // End if $itemSecondColour 

?>

<?php if (!empty($itemThirdColour)) { ?>
newmtl <?php echo $itemThirdColour;?> 
	Ns 10.0000
	Ni 1.5000
	d 1.0000
	Tr 0.0000
	Tf 1.0000 1.0000 1.0000 
	illum 2
	Ka 0.59 0.59 0.59
	Kd 0.59 0.59 0.59
	Ks 0.00 0.00 0.00
	Ke 0.00 0.00 0.00
	map_Kd textures/<?php echo $itemThirdColour; ?>.jpg<?php echo $fileVersion; ?>
<?php } ?>

<?php if (!empty($itemFourthColour)) { ?>
newmtl <?php echo $itemFourthColour;?> 
	Ns 10.0000
	Ni 1.5000
	Tr 0.0000
	Tf 1.0000 1.0000 1.0000 
	illum 2
	Ka 0.59 0.59 0.59
	Kd 0.59 0.59 0.59
	Ks 0.00 0.00 0.00
	Ke 0.00 0.00 0.00
	map_Kd textures/<?php echo $itemFourthColour; ?>.jpg<?php echo $fileVersion; ?>
<?php } ?>
	
<?php if (!empty($itemUnderColour)) { ?>
newmtl <?php echo $itemUnderColour; ?> 
	Ns 50
	Ka 0.150000 0.15000 0.15000
    Kd 0.150000 0.15000 0.15000
    illum 1
<?php } ?>

<?php if (!empty($itemGlassColour)) { ?>
newmtl <?php echo $itemGlassColour; ?> 
	Ns 100.0000
	Ni 1.5000
	Tr 0.7000
	Tf 0.3000 0.3000 0.3000
	illum 2
	Ka 0.5880 0.5880 0.5880
	Kd 0.5880 0.5880 0.5880
	Ks 0.0000 0.0000 0.0000
	Ke 0.0000 0.0000 0.0000
	map_Kd textures/<?php echo $itemGlassColour; ?>.jpg<?php echo $fileVersion; ?>
<?php } ?>

<?php if (!empty($itemMetalColour)) { 

if (in_array($itemMetalColour, $dullmetal)){ ?>

 newmtl <?php echo $itemMetalColour; ?> 
 Ka 0.5000 0.5000 0.5000
 Kd 0.75000 0.75000 0.75000
 Ks 0.180 0.180 0.180
 Tf 0.1000 0.1000 0.1000
 illum 2
 Ns 100
 map_Kd textures/<?php echo $itemMetalColour; ?>.jpg<?php echo $fileVersion; ?>

<?php } else { // if cladded top check that banding colour is not decor nbronze or black. If they are change the mtl settings.  ?>
 
 newmtl <?php echo $itemMetalColour; ?> 
    Ka 1 1 1
    Kd 1 1 0.95
    Ks 1 1 0.95
    Ns 4
    sharpness 0
    illum 0
 map_Kd textures/<?php echo $itemMetalColour; ?>.jpg<?php echo $fileVersion; ?>
 
<?php } ?>
 

<?php } ?>

newmtl cream
	Ka 0.50000 0.5000 0.5000
    Kd 0.55000 0.55000 0.5500
    illum 1

newmtl <?php echo $itemProfileColour; ?>
<?php if (in_array($itemColour, $fullVein)) {  // if top is fullVein  ?>
    
    Ka 0.85 0.85 0.85
    Kd 0.75 0.75 0.75
    Ks 0.5 0.5 0.5
    Ns 2
    Ni 0.5
    illum 0
    sharpness 0
    map_Kd textures/<?php echo $itemColour; ?>-vein.jpg<?php echo $fileVersion;
  } elseif (in_array($itemColour, $yellowProfile)) {  // if top is yellow profile  ?>
  
    Ka 0.50000 0.5000 0.5000
    Kd 0.57000 0.5500 0.5200
    illum 1
<?php } elseif (in_array($itemColour, $darkProfile)) {  // if top is dark profile  ?>
  
    Ka 0.30000 0.3000 0.3000
    Kd 0.250 0.250 0.250
    illum 1
<?php } elseif (in_array($itemColour, $warmProfile)) {  // if top is warm profile  ?>
  
    Ka 0.50000 0.5000 0.5000
    Kd 0.5200 0.5200 0.5000
    illum 1
<?php } else { // cream/white ?>
    Ka 0.50000 0.5000 0.5000
    Kd 0.5000 0.5000 0.5000
    illum 1
<?php } ?>

newmtl <?php echo $itemMeshColour; ?> 
	Ka 0.40000 0.4000 0.4000
    Kd 0.4000 0.4000 0.4000
    illum 1



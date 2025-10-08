<?php
$cadena = 'https://sfpya.edomexico.gob.mx/controlv/faces/rev/t.xhtml?vin=JN6FE56S75X523321&p=NAD8524&t=1&u=xdEIOwzZM7m3i6iM0Z490BcNfNAsh36y4U+fLolvU3Hzr5ucZG0i1G7q7q5z0pJidk7kAMT/9j58Qy8fSh85xXsv3qu/qhl9t3l6y5ScVkFBc9149GUrAIJpoyrTqreR';

$explode = explode('?', $cadena);
//echo $explode[1];

parse_str($explode[1], $datos);
echo base64_encode($datos['u']);

echo '<pre>';
print_r($datos);

echo '</pre>';

?>
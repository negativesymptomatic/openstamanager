<?php

echo '
<div class="row">
    <div class="col-xs-6">
        <img src="'.__DIR__.'/logo_azienda.jpg" alt="Logo" border="0"/>
    </div>
    <div class="col-xs-6 text-right">
        <p><b>$f_ragionesociale$</b></p>
        <p>$f_indirizzo$ $f_citta_full$</p>
        <p>'.(!empty($f_piva) ? tr('P.Iva').': ' : '').'$f_piva$</p>
        <p>'.(!empty($f_codicefiscale) ? tr('C.F.').': ' : '').'$f_codicefiscale$</p>
        <p>'.(!empty($f_capsoc) ? tr('Cap.Soc.').': ' : '').'$f_capsoc$</p>
        <p>'.(!empty($f_telefono) ? tr('Tel').': ' : '').'$f_telefono$</p>
    </div>
</div>';

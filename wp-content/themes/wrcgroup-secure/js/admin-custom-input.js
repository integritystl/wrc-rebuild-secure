//requires jquery.inputmask.js
jQuery(document).ready(function($) {
    var phones = [{ "mask": "(###) ###-####"}];
    $('#custom-phone-input input').inputmask({ 
        mask: phones, 
        greedy: false, 
        definitions: { '#': { validator: "[0-9]", cardinality: 1}} });
});
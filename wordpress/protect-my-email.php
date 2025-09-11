<?php
/**
 * Shortcode:
 *   [js_email label="Reach out to me via Email:" user="me" domain="domain.am" display="inline" subject="Hello" msg="Please enable JS for the Email" class="optional-extra-class"]
 * - Minimal shortcode: [js_email user="me" domain="domain.am"]
 * - Without JS: shows label + fallback message.
 * - With JS: shows label + real mailto link (no flash).
 * - display="block" => tight single line (good for stacking lines)
 * - display="inline" => keep inline with surrounding text
 * - subject is optional, if set adds ?subject= to mailto link
 * - class is optional extra class to add to wrapper span
 * - msg is optional message to show when JS is disabled (default: "Please enable JS for the Email")
 */

add_shortcode('js_email', function($atts){
  $a = shortcode_atts([
    'label'   => 'Email:',
    'user'    => '',
    'domain'  => '',
    'display' => 'inline',          // inline | block
    'subject' => '',
    'msg'     => 'Please enable JS for the Email',
    'class'   => '',                // optional extra class
  ], $atts, 'js_email');

  if (!$a['user'] || !$a['domain']) return '';

  $email   = $a['user'].'@'.$a['domain'];
  $query   = $a['subject'] !== '' ? ('?subject='.rawurlencode($a['subject'])) : '';
  // obfuscate (reverse + base64) so raw email isn't in HTML
  $payload = base64_encode(strrev($email.$query));

  $id      = 'e'.wp_generate_password(6,false,false);
  $isBlock = strtolower($a['display']) === 'block';
  $wrapCls = 'js-email '.($isBlock ? 'block' : 'inline').' '.trim($a['class']);
  $wrapSty = $isBlock ? 'display:block;margin:0;padding:0;' : 'display:inline;margin:0;padding:0;';

  ob_start(); ?>
<span id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($wrapCls); ?>" style="<?php echo esc_attr($wrapSty); ?>">
  <span class="label"><?php echo esc_html(rtrim($a['label'])); ?></span>
  <span class="addr"></span>
  <span class="fallback"><?php echo esc_html($a['msg']); ?></span>
</span>
<script>(function(){var box=document.getElementById('<?php echo $id; ?>');if(!box)return;function dec(s){try{return atob(s).split('').reverse().join('')}catch(e){return''}}
var payload=dec('<?php echo $payload; ?>');if(!payload)return;var parts=payload.split('?'),addr=parts[0],q=parts[1]?'?'+parts[1]:'';var link=document.createElement('a');link.rel='nofollow noopener';link.href='mailto:'+addr+q;link.textContent=addr;link.style.display='inline';link.style.margin='0';link.style.padding='0';var addrSpan=box.querySelector('.addr');if(addrSpan){addrSpan.textContent='';addrSpan.appendChild(link)}
box.classList.add('ready')})();</script>
<?php
  return ob_get_clean();
});

/* Minimal CSS to control visibility + tight spacing */
add_action('wp_head', function(){
  echo '<style>.js-email{margin:0;padding:0;line-height:inherit}.js-email.inline{display:inline}.js-email.block{display:block}.js-email .label{display:inline;margin-right:.25em}.js-email .addr{display:none}.js-email .fallback{display:inline font-weight:700}.js-email.ready .addr{display:inline}.js-email.ready .fallback{display:none}.js-email a{display:inline;margin:0;padding:0;text-decoration:underline}</style>'."\n";
}, 1);

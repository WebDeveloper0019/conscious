<?php
declare(strict_types=1);
function validate_fields(array $data,array $rules): array {
    $errors=[];$clean=[];
    foreach($rules as $field=>$fieldRules){$value=$data[$field]??null;$fieldRules=is_array($fieldRules)?$fieldRules:explode('|',$fieldRules);$required=in_array('required',$fieldRules,true);
        if($required&&($value===null||$value===''||$value===[])){$errors[$field]='This field is required.';continue;}
        if($value===null||$value===''){$clean[$field]=null;continue;}
        foreach($fieldRules as $rule){[$name,$arg]=array_pad(explode(':',(string)$rule,2),2,null);
            if($name==='string'&&!is_string($value))$errors[$field]='Enter valid text.';
            elseif($name==='email'&&!is_valid_email((string)$value))$errors[$field]='Enter a valid email address.';
            elseif($name==='mobile'&&!is_valid_indian_mobile((string)$value))$errors[$field]='Enter a valid Indian mobile number.';
            elseif($name==='pincode'&&!preg_match('/^[1-9][0-9]{5}$/',(string)$value))$errors[$field]='Enter a valid six-digit PIN code.';
            elseif($name==='gstin'&&!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/',strtoupper((string)$value)))$errors[$field]='Enter a valid GSTIN.';
            elseif($name==='ifsc'&&!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/',strtoupper((string)$value)))$errors[$field]='Enter a valid IFSC code.';
            elseif($name==='min'&&is_string($value)&&mb_strlen($value)<(int)$arg)$errors[$field]="Use at least {$arg} characters.";
            elseif($name==='max'&&is_string($value)&&mb_strlen($value)>(int)$arg)$errors[$field]="Use no more than {$arg} characters.";
            elseif($name==='integer'&&filter_var($value,FILTER_VALIDATE_INT)===false)$errors[$field]='Enter a whole number.';
            elseif($name==='numeric'&&!is_numeric($value))$errors[$field]='Enter a valid number.';
            elseif($name==='in'&&!in_array((string)$value,explode(',',(string)$arg),true))$errors[$field]='Select a valid option.';
            elseif($name==='boolean'&&!in_array($value,[true,false,0,1,'0','1'],true))$errors[$field]='Select a valid option.';
        }
        $clean[$field]=is_string($value)?trim($value):$value;
    }
    if($errors)json_response(422,['success'=>false,'message'=>'Please correct the highlighted fields.','errors'=>$errors]); return $clean;
}

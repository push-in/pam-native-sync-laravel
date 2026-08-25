<?php
declare(strict_types=1);
namespace Pam\Native\LaravelSync\Http\Requests;

use Pam\Native\LaravelSync\Domain\SyncOperationKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
final class SyncRequest extends FormRequest
{
 public function authorize():bool{return $this->user()!==null;}
 public function rules():array{$safe=['required','string','max:128','regex:/^[A-Za-z0-9][A-Za-z0-9_.:-]*$/D'];return['clientId'=>$safe,'cursor'=>['nullable','string','max:256',function(string$a,mixed$v,\Closure$fail):void{try{app(\Pam\Native\LaravelSync\CursorCodec::class)->decode(is_string($v)?$v:null);}catch(\InvalidArgumentException){$fail('The cursor is invalid or expired.');}}],'limit'=>['sometimes','integer','min:1','max:'.config('pam-native-sync.max_pull',500)],'operations'=>['present','array','max:'.config('pam-native-sync.max_operations',100)],'operations.*.id'=>$safe,'operations.*.collection'=>$safe,'operations.*.recordId'=>$safe,'operations.*.kind'=>['required','integer',Rule::in(array_column(SyncOperationKind::cases(),'value'))],'operations.*.payload'=>['present','array'],'operations.*.baseVersion'=>['required','integer','min:0'],'operations.*.clientTimestampMillis'=>['required','integer','min:0']];}
 public function after():array{return[function(Validator$v):void{foreach((array)$this->input('operations',[])as$i=>$op)if((int)($op['kind']??0)===SyncOperationKind::Delete->value&&($op['payload']??[])!==[])$v->errors()->add("operations.$i.payload",'Delete operations cannot carry a payload.');}];}
}

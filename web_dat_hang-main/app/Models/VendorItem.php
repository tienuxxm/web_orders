<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class VendorItem extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'view_Purch_ Rcpt_ Line';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = ['Document No_', 'Line No_'];
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getAttribute($keyName));
        }

        return $query;
    }

    protected $fillable = [
        'Document No_', 
        'Line No_',
        'Buy-from Vendor No_',
        'Shortcut Dimension 1 Code', 
        'No_' 
    ];
    
    
}
<?php
declare(strict_types=1);

function india_states_and_uts(): array {
    return [
        'Andaman and Nicobar Islands','Andhra Pradesh','Arunachal Pradesh','Assam','Bihar',
        'Chandigarh','Chhattisgarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Goa',
        'Gujarat','Haryana','Himachal Pradesh','Jammu and Kashmir','Jharkhand','Karnataka',
        'Kerala','Ladakh','Lakshadweep','Madhya Pradesh','Maharashtra','Manipur','Meghalaya',
        'Mizoram','Nagaland','Odisha','Puducherry','Punjab','Rajasthan','Sikkim','Tamil Nadu',
        'Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal',
    ];
}
function canonical_india_state(string $input): ?string {
    $input = trim(preg_replace('/\s+/u',' ',$input) ?? $input);
    foreach (india_states_and_uts() as $state) if (mb_strtolower($state) === mb_strtolower($input)) return $state;
    return null;
}

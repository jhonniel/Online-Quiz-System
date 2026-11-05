<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\OmadaRequest;
use App\Http\Requests\Admin\StarlinkRequest;
use App\Models\Omada;
use App\Models\Starlink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DeviceIdentifierValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  class-string<FormRequest>  $requestClass
     * @return array<string, array<int, string>>
     */
    private function errorsFor(string $requestClass, array $data, ?string $routeParam = null, ?Model $model = null): array
    {
        $request = $requestClass::create('/', 'POST', $data);
        if ($routeParam && $model) {
            $route = (new Route('PUT', '/{'.$routeParam.'}', []))->bind($request);
            $route->setParameter($routeParam, $model);
            $request->setRouteResolver(fn () => $route);
        }

        return Validator::make($data, $request->rules(), $request->messages())->errors()->toArray();
    }

    public function test_starlink_requires_serial_and_kit_number(): void
    {
        $errors = $this->errorsFor(StarlinkRequest::class, []);

        $this->assertSame(['Serial number is required.'], $errors['serial_number']);
        $this->assertSame(['Kit number is required.'], $errors['kit_number']);
    }

    public function test_starlink_rejects_duplicate_serial_and_kit_ignoring_case_and_spaces(): void
    {
        Starlink::create(['serial_number' => 'SN-001', 'kit_number' => 'KIT-001']);

        $errors = $this->errorsFor(StarlinkRequest::class, [
            'serial_number' => ' sn-001 ',
            'kit_number' => 'kit-001',
        ]);

        $this->assertArrayHasKey('serial_number', $errors);
        $this->assertArrayHasKey('kit_number', $errors);
    }

    public function test_starlink_edit_keeps_its_own_numbers_but_not_another_devices(): void
    {
        $device = Starlink::create(['serial_number' => 'SN-001', 'kit_number' => 'KIT-001']);
        Starlink::create(['serial_number' => 'SN-002', 'kit_number' => 'KIT-002']);

        $ownNumbers = $this->errorsFor(StarlinkRequest::class, [
            'serial_number' => 'SN-001',
            'kit_number' => 'KIT-001',
        ], 'starlink', $device);
        $this->assertSame([], $ownNumbers);

        $takenNumbers = $this->errorsFor(StarlinkRequest::class, [
            'serial_number' => 'SN-002',
            'kit_number' => 'KIT-001',
        ], 'starlink', $device);
        $this->assertArrayHasKey('serial_number', $takenNumbers);
        $this->assertArrayNotHasKey('kit_number', $takenNumbers);
    }

    public function test_omada_requires_serial_and_rejects_duplicates(): void
    {
        $this->assertArrayHasKey('serial_number', $this->errorsFor(OmadaRequest::class, []));

        Omada::create(['serial_number' => 'OM-001', 'mac_address' => 'AA:BB:CC:DD:EE:FF']);

        $errors = $this->errorsFor(OmadaRequest::class, [
            'serial_number' => 'om-001',
            'mac_address' => 'aa:bb:cc:dd:ee:ff',
        ]);

        $this->assertArrayHasKey('serial_number', $errors);
        $this->assertArrayHasKey('mac_address', $errors);
    }
}

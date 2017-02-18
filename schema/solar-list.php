<?

class DataService extends Injectable
{
    public function getSnapshot() { } // OLD
    public function getChartData($prj, $dev, $fld) { } // OLD

    public function getIRR($prj, $period) { }
    public function getTMP($prj, $period) { }
    public function getKW($prj,  $period) { }

    protected function getEnvKitCriteria($prj,   $devcode, $period) { }
    protected function getInverterCriteria($prj, $devcode, $period) { }

    protected function getPeriod($period) { }

    public function getRefData($prj, $year, $month) { }

    public function getPR($prj) { }
}

class ProjectService extends Injectable
{
    public function getAll(/* $includeInactive = false */) { }
    public function get($id) { }

    public function getName($id) { }
    public function getFtpDir($id) { }
    public function getDcSize($id) { }
    public function getAcSize($id) { }

    public function activate($id) { }
    public function deactivate($id) { }

    public function add($info) { }

    public function getDetails($id) { }
}

class DeviceService extends Injectable
{
    public function getAll() { }

    public function getDevices($prj) { }
    public function getDevice($prj, $dev) { }

    public function getInverters($prj) { }
    public function getGenMeter($prj) { }
    public function getEnvKit($prj) { }
    public function getDevicesOfType($prj, $type) { }

    public function getTable($prj, $dev) { }
    public function getTableColumns($prj, $dev) { }
    public function getModelName($prj, $dev) { }

    public function add($projectId, $devices) { }
}





class SnapshotService extends Injectable
{
    public function load() { }
    public function generate() { }

    protected function getGCPR($prj) { }
    protected function getCurrentPower($prj) { }
    protected function getIrradiance($prj) { }
    protected function getInvertersGenerating($prj) { }
    protected function getDevicesCommunicating($prj) { }
    protected function getLastCom($prj) { }

    protected function getAvgIrradiancePOA($prj) { return '0.0'; }
    protected function getAvgModuleTemp($prj)    { return '0.0'; }
    protected function getMeasuredEnergy($prj)   { return '0.0'; }
}

class UserService extends Injectable
{
    public function getAll() { }
    public function get($id) { }

    public function find($name) { }

    public function activate($id) { }
    public function deactivate($id) { }

    public function changePassword($id, $newPassword) { }
    public function add($info) { }
}

class ReportService extends Injectable
{
    public function genDailyReport() { }
    public function sendDailyReport() { }
    public function genMonthlyReport() { }
    public function sendMonthlyReport() { }
}

class ExportService extends Injectable
{
    public function exportMinute1($start, $end) { }
    public function exportMinute15($start, $end) { }
    public function exportHour1($start, $end) { }
    public function exportDaily($start, $end) { }
    public function exportMonthly($start, $end) { }
}

class SolarService extends Injectable { }

<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\System\Project;

class ProjectService extends Injectable
{
    protected $projects = [];

    public function getAll(/* $includeInactive = false */)
    {
        if (!$this->projects) {
           #$sql = "SELECT * FROM projects WHERE active=1";
            $sql = "SELECT p.*, c.cbdir
                      FROM projects p
                 LEFT JOIN project_combiner c ON p.id=c.project_id
                     WHERE p.active=1
                  ORDER BY p.id";
            $projects = $this->db->fetchAll($sql);

            foreach ($projects as $project) {
                $id = $project['id'];
                $object = new Project($project);
                $this->projects[$id] = $object;
            }

            // Load all devices, then attach them to project
            $sql = "
				SELECT d.*,
					inv.inverter_number,
					inv.inverter_label,
					inv.inverter_name,
					inv.inverter_ip,
					inv.inverter_file,
					inv.recombiner_ip,
					inv.recombiner_file,
					inv.ihouse_number
				FROM devices d
				LEFT JOIN inverter_details inv ON inv.project_id=d.project_id AND d.devcode=inv.inverter_file";

            $devices = $this->db->fetchAll($sql);

            foreach ($devices as $device) {
                $projectId = $device['project_id'];
                if (isset($this->projects[$projectId])) {
                    $project = $this->projects[$projectId];
                    $project->initDevices($device);
                }
            }

            // Load all cameras, then attach them to project
            $sql = "SELECT * FROM project_camera";
            $cameras = $this->db->fetchAll($sql);

            foreach ($cameras as $camera) {
                $projectId = $camera['project_id'];
                if (isset($this->projects[$projectId])) {
                    $project = $this->projects[$projectId];
                    $project->addCamera($camera);
                }
            }

            // Load all camera-links, then attach them to project
            $sql = "SELECT * FROM camera_link";
            $links = $this->db->fetchAll($sql);

            foreach ($links as $link) {
                $projectId = $link['project_id'];
                if (isset($this->projects[$projectId])) {
                    $project = $this->projects[$projectId];
                    $project->cameraLink = $link['link'];
                }
            }
        }

#       unset($this->projects[7]); // remove Norfolk, it affects everywhere

        return $this->projects;
    }

    public function get($id)
    {
        if (!$this->projects) {
            $this->getAll();
        }
        if (isset($this->projects[$id])) {
            return $this->projects[$id];
        }
        throw new \Exception("Invalid Parameter: $id");
    }

    public function getCombinerProjects()
    {
        if (!$this->projects) {
            $this->getAll();
        }

        $projects = [];
        foreach ($this->projects as $project) {
            if (count($project->combiners) > 0) {
                $projects[] = $project;
            }
        }

        return $projects;
    }

    public function getDetails($id)
    {
        $details = [];

        $project = $this->get($id);

        $details['project_name'] = $project->name;
        $details['project_id'] = $project->id;
        $details['address'] = $project->name;
        $details['ac_size'] = round($project->capacityAC);
        $details['dc_size'] = round($project->capacityDC);
        $details['num_of_inverters'] = max(1, count($project->inverters));
        $details['num_of_genmeters'] = count($project->genmeters);
        $details['num_of_envkits'] = count($project->envkits);

        $report = $this->dailyReportService->load(date('Y-m-d', strtotime('-1 day')));

        $details['yesterday']['prod'] = round($report[$id]['Measured_Production']);
        $details['yesterday']['inso'] = round($report[$id]['Measured_Insolation'], 1);
        $details['month-to-date']['prod'] = round($report[$id]['Total_Energy']);
        $details['month-to-date']['inso'] = round($report[$id]['Total_Insolation']);
        $details['today']['prod'] = round($project->getKW('TODAY'));
        $details['today']['inso'] = round($project->getIRR('TODAY') / 1000.0, 1);

        $getVal = function($data, $fields) {
            foreach ($fields as $name) {
                if (isset($data[$name])) {
                   return round($data[$name]);
                }
            }
            return '';
        };

        // Inverters
        $details['inverters'] = [];
        $details['inverter_type'] = '';
        foreach ($project->inverters as $inverter) {
            $code = $inverter->code;
            $data = $inverter->getLatestData();

            // empty if no data over 30 minutes
            if (abs(time() - strtotime($data['time'].' utc') > 1800)) {
                foreach ($data as $key => $val) {
                    $data[$key] = '';
                }
            }

            $details['inverters'][$code]['data'] = $data;
            $details['inverters'][$code]['model'] = $inverter->model;
            $details['inverters'][$code]['ip'] = $inverter->inverterIp;
            $details['inverters'][$code]['label'] = $inverter->inverterLabel;
            $details['inverters'][$code]['number'] = $inverter->inverterNumber;

            $details['inverter_type'] = $inverter->getInverterType();

            $power = $getVal($data, ['kw', 'line_kw']);

            $details['inverters'][$code]['type']   = $inverter->getInverterType();
            $details['inverters'][$code]['power']  = $power;
            $details['inverters'][$code]['status'] = $power > 0 ? 'On' : 'Off';
            $details['inverters'][$code]['fault']  = 'None';
            $details['inverters'][$code]['vla']    = $getVal($data, ['vln_a', 'volt_a', 'volts_a']);
            $details['inverters'][$code]['vlb']    = $getVal($data, ['vln_b', 'volt_b', 'volts_b']);
            $details['inverters'][$code]['vlc']    = $getVal($data, ['vln_c', 'volt_c', 'volts_c']);

            $details['inverters'][$code]['combiner'] = '';
            if ($combiner = $inverter->getCombiner()) {
                $details['inverters'][$code]['combiner'] = $project->id.'_'.$combiner;
            }
        }

        // Envkit
        foreach ($project->envkits as $envkit) {
            $code = $envkit->code;
            $data = $envkit->getLatestData();

            // empty if no data over 30 minutes
            if (abs(time() - strtotime($data['time'].' utc') > 1800)) {
                foreach ($data as $key => $val) {
                    $data[$key] = '';
                }
            }

            $details['envkits'][$code]['inso'] = round($data['IRR']);
            $details['envkits'][$code]['oat'] = round($data['OAT']);
            $details['envkits'][$code]['panelt'] = round($data['PANELT']);
        }

        // GenMeter
        foreach ($project->genmeters as $genmeter) {
            $code = $genmeter->code;
            $data = $genmeter->getLatestData();

            // empty if no data over 30 minutes
            if (abs(time() - strtotime($data['time'].' utc') > 1800)) {
                foreach ($data as $key => $val) {
                    $data[$key] = '';
                }
            }

            $details['genmeters'][$code]['kw-del'] = abs(round($data['kwh_del']));
            $details['genmeters'][$code]['kw-rec'] = abs(round($data['kwh_rec']));
            $details['genmeters'][$code]['kvar'] = round($data['kva']);
            $details['genmeters'][$code]['vla'] = round($data['vln_a']);
            $details['genmeters'][$code]['vlb'] = round($data['vln_b']);
            $details['genmeters'][$code]['vlc'] = round($data['vln_c']);
        }

        // if there is no inverter
        if (count($project->inverters) == 0) {
            $genmeter = current($details['genmeters']);
            $details['inverters']['fake']['type']   = '';
            $details['inverters']['fake']['power']  = $genmeter['kvar'];
            $details['inverters']['fake']['status'] = 'On';
            $details['inverters']['fake']['fault']  = 'None';
            $details['inverters']['fake']['vla']    = $genmeter['vla'];
            $details['inverters']['fake']['vlb']    = $genmeter['vlb'];
            $details['inverters']['fake']['vlc']    = $genmeter['vlc'];
        }

        // THIS IS REALLY BAD HACK! SITE DELETED!
       #if (in_array($id, [ 37, 38, 39 ])) {
       #    foreach ($project->combiners as $combiner) {
       #        $details['obvius_a8332_combiner'][] = $combiner->getLatestData();
       #    }
       #}

        return $details;
    }

    public function loadCombiner($prj, $dev)
    {
        $project = $this->get($prj);
        $combiner = $project->combiners[$dev];
        $data = $combiner->load();
        return $data;
    }

    // Combiner Baseline
    public function getCombinerPerformance($prj)
    {
        $sql = "SELECT * FROM combiner_details WHERE project_id='$prj'";
        $rows = $this->db->fetchAll($sql);

        $invs = [];
        foreach ($rows as $key => $row) {
            $devcode = $row['devcode'];
            $invs[$devcode][] = $row;
        }

        $project = $this->get($prj);

        $perfs = [];
        foreach ($invs as $devcode => $cbs) {
            if (in_array($prj, [40, 48])) {
                $combiner = $project->inverters[$devcode];
            } else {
                $combiner = $project->combiners[$devcode];
            }
            $data = $combiner->load(1);
            $latest = $data[0];
            foreach ($cbs as $cb) {
                $cbseq = $cb['cb_seq'];
                $raw = $latest[$cbseq];

                $numModules = $cb['num_modules'];
                $moduleRating = $cb['module_rating'];
                $perfs[] = 10000*$raw/$numModules/$moduleRating;
            }
        }

        asort($perfs);
        $perfs = array_slice($perfs, 10);

        return array_sum($perfs)/count($perfs);
    }

    public function loadInverterDetails($prj, $inv)
    {
        $sql = "SELECT * FROM inverter_details WHERE project_id='$prj' AND inverter_number='$inv'";
        $row = $this->db->fetchOne($sql);
        return $row;
    }

    public function loadCombinerDetails($prj, $inv)
    {
        $inverter = $this->loadInverterDetails($prj, $inv);
        $sql = "SELECT * FROM combiner_details WHERE project_id='$prj' AND inv_seq='$inv'";

        $data = $this->db->fetchAll($sql);

        $dev = $inverter['recombiner_file']; // Like mb-010

        $project = $this->projectService->get($prj);
        if (in_array($prj, [40, 48])) {
            $combiner = $project->inverters[$dev];
        } else {
            $combiner = $project->combiners[$dev];
        }
        $tmpdata = $combiner->load(1);
        $latest = $tmpdata[0];

        $baseline = $this->getCombinerPerformance($prj);

        foreach ($data as $key => $row) {
            $cbseq = $row['cb_seq'];

            $raw = $latest[$cbseq];
            $normalized = round($raw/$row['num_strings'], 2);

            $data[$key]['raw'] = $raw;
            $data[$key]['normalized'] = $normalized;

            // check if under performance
            $numModules = $row['num_modules'];
            $moduleRating = $row['module_rating'];
            $perf = 10000*$raw/$numModules/$moduleRating;

            if ($perf < $baseline*0.9) {
                $data[$key]['low_perf'] = 1;
            }
        }

        return $data;
    }

    // This is for project-40/48 only
    public function loadSandhurstInverter($prj, $dev)
    {
        $project = $this->get($prj);
        $inverter = $project->inverters[$dev];
        $data = $inverter->loadData(12);
        return $data;
    }

    // This is for project-40/48 only
    public function loadStringLevelCombiner($prj, $dev)
    {
        $project = $this->get($prj);
        $combiner = $project->combiners[$dev];
        $data = $combiner->loadData(1);
        return $data[0];
    }
}

<?php namespace GO;

class Scheduler
{

  /**
   * Timezone
   */
  private $timezone = 'Europe/Dublin';

  /**
   * The jobs that need to run now
   */
  private $jobs = [];

  /**
   * Where to send the output of the job
   */
  private $output = '/dev/null';

  /**
   * PHP binary
   */
  private $phpbin = PHP_BINARY;

  /**
   * Init the datetime
   *
   */
  public function __construct()
  {
    $this->dt = new \DateTime('now');
    $this->dt->setTimezone(new \DateTimeZone($this->timezone));
  }

  /**
   * Run the scheduled commands
   *
   */
  public function run()
  {
    if (count($this->jobs) == 0) {
      echo 'Nothing to do' . PHP_EOL;
      exit(1);
    }

    foreach ($this->jobs as $j) {
      echo 'Executing ' . $j . PHP_EOL;
      $this->exec($j);
    }
  }

  /**
   * Schedule a command at a given time
   *
   * @param [string] $command - command to execute
   * @param [string] $cron - cron schedule
   *
   */
  public function schedule($command, $cron)
  {
    if ($this->isDue($cron)) {
      array_push($this->jobs, $command);
    }
  }

  /**
   * Check if a schedule is due now
   *
   * @param [string] $cron - cron schedule
   *
   * @return [bool]
   */
  private function isDue($cron)
  {
    $time = explode(' ', $cron);

    $due = [
      'minute'        => $time[0],
      'hour'          => $time[1],
      'dayOfTheMonth' => $time[2],
      'month'         => $time[3],
      'dayOfTheWeek'  => $time[4],
    ];

    $now = [
      'minute'        => $this->dt->format('i'),
      'hour'          => $this->dt->format('H'),
      'dayOfTheMonth' => $this->dt->format('d'),
      'month'         => $this->dt->format('m'),
    ];

    if ($due['minute'] != $now['minute'] && $due['minute'] != '*') {
      return false;
    }

    if ($due['hour'] != $now['hour'] && $due['hour'] != '*') {
      return false;
    }

    if ($due['dayOfTheMonth'] != $now['dayOfTheMonth'] && $due['dayOfTheMonth'] != '*') {
      return false;
    }

    if ($due['month'] != $now['month'] && $due['month'] != '*') {
      return false;
    }

    return true;
  }

  /**
   * Execute the command
   *
   * @param [string] $command - command to execute
   *
   */
  private function exec($command)
  {
    $script = explode(' ', basename($command))[0];

    $output = is_dir($this->output) ? $this->output.'/'.(str_replace('.', '_', $script)).'.log' : $this->output;
    
    $command = $this->phpbin . ' ' . $command . ' 1>> ' . $output . ' 2>&1 &';
    echo 'Exec ' . $command . PHP_EOL;
    exec($command);
  }

  /**
   * Set the timezone
   *
   * @param [string] timezone
   *
   */
  public function setTimezone($timezone)
  {
    $this->timezone = $timezone;
  }

  /**
   * Set where to send the output
   *
   * @param [string] $output - path file or folder, if a folder is specified,
   *                           in that folder will be created several files,
   *                           one for each scheduled command
   *
   */
  public function setOutput($output)
  {
    $this->output = $output;
  }

  /**
   * Set the php binary
   *
   * @param [string] $bin - path to php binary
   *
   */
  public function setPHPBin($bin)
  {
    $this->phpbin = $bin;
  }

}

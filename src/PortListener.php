<?php namespace WillWashburn;

use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestListener as TestListenerInterface;
use PHPUnit_Framework_TestSuite;

/**
 * @package WillWashburn
 */
class PortListener implements TestListenerInterface
{

    private $runningExecProcess = null;

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) { }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) { }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) { }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time) { }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) { }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite) { }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite) { }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $pid = getmypid();

        $cmd = "lsof -p $pid | grep TCP >> tmp.txt";

        $bash  = <<<EOF
while true
do
    $cmd
done

EOF;
        $pipes = [];

        $this->runningExecProcess = proc_open($bash, [], $pipes);

    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {

        $lines = [];

        $s = proc_get_status($this->runningExecProcess);
        posix_kill($s['pid'], SIGKILL);
        proc_close($this->runningExecProcess);

        if ( file_exists('tmp.txt') ) {

            $contents = file('tmp.txt');
            $lines    = array_unique($contents);
            unlink('tmp.txt');
        }


        if ( count($lines) > 0 ) {

            $message = $test->toString() . ' - There were network requests made across a port' . PHP_EOL . implode('', $lines);

            throw new PHPUnit_Framework_AssertionFailedError($message);
        }

    }
}
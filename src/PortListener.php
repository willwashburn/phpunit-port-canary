<?php namespace WillWashburn;

use PHPUnit_Framework_BaseTestListener;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestListener as TestListenerInterface;
use PHPUnit_Framework_TestSuite;

/**
 */
class PortListener extends PHPUnit_Framework_BaseTestListener implements TestListenerInterface
{

    private $runningExecProcess = null;

    private $leakyTests = [];

    private $suites;

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites++;
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites--;

        // We only want to show the message at the end of the last test suite
        if ( $this->suites === 0 ) {
            $this->renderReport();
        }
    }

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
        $s = proc_get_status($this->runningExecProcess);
        posix_kill($s['pid'], SIGKILL);
        proc_close($this->runningExecProcess);

        $lines = [];
        if ( file_exists('tmp.txt') ) {

            $contents = file('tmp.txt');
            $lines    = array_unique($contents);
            unlink('tmp.txt');
        }

        if ( count($lines) > 0 ) {

            $details = [];
            foreach ( $lines as $line ) {

                $data      = explode(' ', $line);
                $details[] = array_filter($data, function ($string) {
                    return strpos($string, 'http') !== false;
                });
            }

            $details = array_unique($details);

            $this->leakyTests[$test->toString()] = $details;

        }

    }

    /**
     * Prints a report of the leaky tests
     *
     */
    protected function renderReport()
    {
        $leaky_tests = count(array_keys($this->leakyTests));

        if ( $leaky_tests > 0 ) {
            $message = sprintf("There are %s tests that are crossing ports!\n", $leaky_tests);

            $this->write("\n\n");
            $this->writeWithColor('bg-yellow', $message);


            foreach ( $this->leakyTests as $testName => $details ) {

                $this->writeWithColor('bold', $testName . " has tests that make requests across a port");
                foreach ( $details as $detail ) {
                    foreach ( $detail as $location ) {
                        $this->write("-->$location\n");
                    }
                }
            }
        }
    }

    /**
     * Formats a buffer with a specified ANSI color sequence if colors are
     * enabled.
     *
     * @param  string $color
     * @param  string $buffer
     *
     * @return string
     * @since  Method available since Release 4.0.0
     */
    private function formatWithColor($color, $buffer)
    {
        $ansiCodes = array(
            'bold'      => 1,
            'fg-black'  => 30,
            'fg-red'    => 31,
            'fg-yellow' => 33,
            'fg-cyan'   => 36,
            'fg-white'  => 37,
            'bg-red'    => 41,
            'bg-green'  => 42,
            'bg-yellow' => 43,
        );

        $codes     = array_map('trim', explode(',', $color));
        $lines     = explode("\n", $buffer);
        $padding   = max(array_map('strlen', $lines));

        $styles = array();
        foreach ( $codes as $code ) {
            $styles[] = $ansiCodes[$code];
        }
        $style = sprintf("\x1b[%sm", implode(';', $styles));

        $styledLines = array();
        foreach ( $lines as $line ) {
            $styledLines[] = $style . str_pad($line, $padding) . "\x1b[0m";
        }

        return implode("\n", $styledLines);
    }

    /**
     * Writes a buffer out with a color sequence if colors are enabled.
     *
     * @param string $color
     * @param string $buffer
     *
     * @since  Method available since Release 4.0.0
     */
    private function writeWithColor($color, $buffer)
    {
        $buffer = $this->formatWithColor($color, $buffer);
        $this->write($buffer . "\n");
    }

    /**
     * @param string $buffer
     */
    private function write($buffer)
    {
        if ( PHP_SAPI != 'cli' ) {
            $buffer = htmlspecialchars($buffer);
        }

        print $buffer;
    }
}
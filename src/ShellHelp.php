<?php
/*
 * PoiXson phpUtils - PHP Utilities Library
 * @copyright 2004-2017
 * @license GPL-3
 * @author lorenzo at poixson.com
 * @link http://poixson.com/
 */
namespace pxn\phpUtils;


class ShellHelp {

	const HELP_WIDTH = 80;

	protected $name = NULL;
	protected $msg  = NULL;

	protected $command  = NULL;
	protected $commands = [];

	protected $flags     = [
		'pre' => [],
		'mid' => [],
		'pst' => []
	];



	public function __construct($command=NULL) {
		$this->setCommand($command);
	}



	public function setSelfName($name) {
		$this->name =
			empty($name)
			? NULL
			: (string) $name;
		return $this;
	}
	public function getSelfName() {
		if (empty($this->name)) {
			return \basename($_SERVER['PHP_SELF']);
		}
		return (string) $this->name;
	}



	public function setMessage($msg=NULL) {
		$this->msg =
			empty($msg)
			? NULL
			: (string) $msg;
		return $this;
	}



	public function setCommand($command) {
		$this->command =
			empty($command)
			? NULL
			: (string) $command;
		return $this;
	}
	public function addCommands($commands) {
		$this->commands = \array_merge(
			$this->commands,
			$commands
		);
		return $this;
	}



	public function addFlags($flags, $position=NULL) {
		$position = (string) $position;
		$position = \mb_strtolower($position);
		$pos = 'mid';
		if ($position == 'pre') {
			$pos = 'pre';
		} else
		if ($position == 'pst' || $position == 'post') {
			$pos = 'pst';
		}
		$this->flags[$pos] = \array_merge(
			$this->flags[$pos],
			$flags
		);
		return $this;
	}



	public function Display() {
		echo "\n";
		$this->Display_Usage();
		$this->Display_Commands();
		$this->Display_Flags();
	}
	public function Display_Usage() {
		$usage = [];
		$usage[] = $this->getSelfName();
		if (\count($this->commands) > 0) {
			$usage[] = (
				empty($this->command)
				? '<command>'
				: (string) $this->command
			);
		}
		$usage[] = '[flags]';
		$usageStr = \implode($usage, ' ');
		unset($usage);
		if (!empty($this->msg)) {
			$msg = Strings::Trim($this->msg);
			$lines = Strings::WrapLines($msg, self::HELP_WIDTH);
			foreach ($lines as $line) {
				echo "{$line}\n";
			}
		}
		echo ShellTools::FormatString(
			"{color=orange}Usage:{reset}\n".
			"  {$usageStr}\n"
		);
		echo "\n";
	}
	public function Display_Commands() {
		echo ShellTools::FormatString(
			"{color=orange}Commands:{reset}\n"
		);
		// find max command length
		$maxSize = 0;
		foreach ($this->commands as $command => $desc) {
			$len = \mb_strlen($command);
			if ($len > $maxSize) {
				$maxSize = $len;
			}
		}
		$maxSize += 3;
		// display commands
		foreach ($this->commands as $command => $desc) {
			$padding = \str_repeat(' ', $maxSize - \mb_strlen($command) );
			$lines = Strings::WrapLines($desc, self::HELP_WIDTH - $maxSize);
			$firstLine = $lines[0];
			echo ShellTools::FormatString(
				"  {color=green}$command{reset}$padding{$firstLine}\n"
			);
			// multi-line description
			if (\count($lines) > 1) {
				unset($lines[0]);
				foreach ($lines as $line) {
					$padding = \str_repeat(' ', $maxSize + 2);
					echo "{$padding}{$line}\n";
				}
			}
		}
		echo "\n";
	}
	public function Display_Flags() {
		echo ShellTools::FormatString(
			"{color=orange}Flags:{reset}\n"
		);
		$firstGroup = (
			isset($this->flags['pre'])
			? $this->flags['pre']
			: []
		);
		$lastGroup = (
			isset($this->flags['pst'])
			? $this->flags['pst']
			: []
		);
		// display flag groups
		$this->Display_FlagGroup('pre', $firstGroup);
		foreach ($this->flags as $group => $flags) {
			if ($group == 'pre' || $group == 'pst') {
				continue;
			}
			$this->Display_FlagGroup($group, $flags);
		}
		$this->Display_FlagGroup('pst', $lastGroup);
	}
	public function Display_FlagGroup($group, $flags) {
		if (!\is_array($flags) || \count($flags) == 0) {
			return;
		}
		$prepared = [];
		$maxSize = 0;
		foreach ($flags as $desc => $entries) {
			// prepare flag strings
			$singles = [];
			$doubles = [];
			foreach ($entries as $entry) {
				$str = Strings::TrimFront($entry, '-');
				if (empty($str)) {
					continue;
				}
				if (\mb_strlen($str) == 1) {
					$singles[] = Strings::ForceStartsWith($entry, '-');
				} else {
					while(!Strings::StartsWith($entry, '--')) {
						$entry = "-{$entry}";
					}
					$doubles[] = $entry;
				}
			}
			// prepare string "-f, --flag"
			$flagStr = \implode(
				\array_merge(
					$singles,
					$doubles
				),
				', '
			);
			// find max length
			$len = \mb_strlen($flagStr);
			if ($len > $maxSize) {
				$maxSize = $len;
			}
			$prepared[$desc] = $flagStr;
		}
		$maxSize += 3;
		// display prepared group of flags
		foreach ($prepared as $desc => $flagStr) {
			$padding = \str_repeat(' ', $maxSize - \mb_strlen($flagStr) );
			$lines = Strings::WrapLines($desc, self::HELP_WIDTH - $maxSize);
			$firstLine = $lines[0];
			echo ShellTools::FormatString(
				"  {color=green}$flagStr{reset}$padding{$firstLine}\n"
			);
			// multi-line description
			if (\count($lines) > 1) {
				unset($lines[0]);
				foreach ($lines as $line) {
					$padding = \str_repeat(' ', $maxSize + 2);
					echo "{$padding}{$line}\n";
				}
			}
		}
		echo "\n";
	}



}

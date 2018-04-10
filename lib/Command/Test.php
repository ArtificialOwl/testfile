<?php
/**
 * TestFile - is getContent Locking your files ?
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2018
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\TestFile\Command;

use Exception;
use OC\Core\Command\Base;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Test extends Base {


	/** @var IRootFolder */
	private $rootFolder;

	/**
	 * Index constructor.
	 *
	 * @param IRootFolder $rootFolder
	 */
	public function __construct(IRootFolder $rootFolder) {
		parent::__construct();

		$this->rootFolder = $rootFolder;
	}


	/**
	 *
	 */
	protected function configure() {
		parent::configure();
		$this->setName('test:file')
			 ->addArgument('userid', InputArgument::REQUIRED, 'user id')
			 ->addArgument('fileid', InputArgument::REQUIRED, 'file id')
			 ->setDescription('is getContent locking your files ?');
	}


	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @throws Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$userId = $input->getArgument('userid');
		$fileId = $input->getArgument('fileid');

		$output->writeln('userId: ' . $userId);
		$output->writeln('fileId: ' . $fileId);

		try {
			$files = $this->rootFolder->getUserFolder($userId)
									  ->getById($fileId);
		} catch (Exception $e) {
			throw new Exception('user does not exist or whatever');
		}

		if (sizeof($files) === 0) {
			throw new Exception('no file with this Id');
		}

		$file = array_shift($files);

		$this->test($output, $file);
	}


	/**
	 * @param OutputInterface $output
	 * @param File $file
	 * @param bool $forceUnlock
	 *
	 * @throws Exception
	 */
	private function test(OutputInterface $output, File $file) {
		$output->writeln('> running the test with file ' . $file->getPath());

		$content = $file->getContent();

		$output->writeln('> file is open, this is its content: ');
		$output->writeln('------');
		$output->writeln($content);
		$output->writeln('------');

		$output->write('-> now we keep cycling ..');
		while (true) {
			if ($this->hasBeenInterrupted()) {
				throw new Exception('! Interrupted by user.');
			}

			$output->write('.');

			sleep(10);
		}
	}


}




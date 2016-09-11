<?php

namespace PhpTabs\Reader\Midi;

use PhpTabs\Component\File;
use PhpTabs\Component\Log;
use PhpTabs\Model\Song;

abstract class MidiReaderBase implements MidiReaderInterface
{
  /** @var File */
  private $file;

  /**
   * @param File $file input file to read
   */
  public function __construct(File $file)
  {
    $this->file = $file;
  }

  /**
   * Reads a 32 bit integer big endian
   * 
   * @return integer
   */
  protected function readInt()
  {
    $bytes = $this->readBytesBigEndian(4);

    return ($bytes[3] & 0xff) | (($bytes[2] & 0xff) << 8) 
      | (($bytes[1] & 0xff) << 16) | (($bytes[0] & 0xff) << 24);
  }

  /**
   * Reads a 16 bit integer big endian
   * 
   * @return integer
   */
  protected function readShort()
  {
    $bytes = $this->readBytesBigEndian(2);

    return (($bytes[0] & 0xff) << 8) | ($bytes[1] & 0xff);
  }

  /**
   * Reads an unsigned 16 bit integer big endian
   * 
   * @return integer
   */
  protected function readUnsignedShort()
  {
    $bytes = $this->readBytesBigEndian(2);

    return (($bytes[0] & 0x7f) << 8) | ($bytes[1] & 0xff);
  }

  /**
   * @param MidiTrackReaderHelper $helper
   * @return integer
   */
  public function readVariableLengthQuantity(MidiTrackReaderHelper $helper)
  {
    $count = 0;
    $value = 0;
    while ($count < 4)
    {
      $data = $this->readUnsignedByte();
      $helper->remainingBytes--;
      $count++;
      $value <<= 7;
      $value |= ($data & 0x7f);
      if ($data < 128)
      {
        return $value;
      }
    }
    throw new \Exception("not a MIDI file: unterminated variable-length quantity");
  }

  /**
   * Reads an unsigned byte
   * 
   * @return byte
   */
  protected function readUnsignedByte()
  {
    return unpack('C', $this->file->getStream())[1];
  }

  /**
   * Skips a sequence
   * 
   * @param integer $num
   */
  protected function skip($num = 1)
  {
    $this->file->getStream($num); 
  }

  /**
   * Reads bytes
   * 
   * @param integer $num
   * @return array An array of bytes
   */
  protected function readBytesBigEndian($num = 1)
  {
    $bytes = array();

    for($i=0; $i<$num; $i++)
    {
      $bytes[$i] = ord($this->file->getStream());
    }

    return $bytes;
  }

  /**
   * Closes the File read
   * 
   */
  protected function closeStream()
  {
    $this->file->closeStream(); 
  }
}

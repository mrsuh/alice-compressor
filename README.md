# Alice compressor

Compression text [file](./data/alice.txt) **Alice's Adventures in Wonderland**

![](./alice.png)

## Algorithms

* [Naive](#naive)
* [RLE](#rle)
* [LZ77](#lz77)
* [LZ77M](#lz77m)

## Naive

### Structure

| dict bit | dict size | dict word length | dict word |
|----------|-----------|------------------|-----------|
| 1 bit    | 7 bit     | 8 bit            | N bit     |

| dict bit | text/dict bit | text length | text  | dict bit | text/dict bit | dict word index |
|----------|---------------|-------------|-------|----------|---------------|-----------------|
| 1 bit    | 1 bit         | 6 bit       | N bit | 1 bit    | 1 bit         | 6 bit           |


### Example 

`ccc ab ab`

| dict bit | dict size | dict word length | dict word |
|----------|-----------|------------------|-----------|
| 1        | 1         | 3                | _ab       |

| dict bit | text/dict bit | text length | text | dict bit | text/dict bit | dict word index | dict bit | text/dict bit | dict word index |
|----------|---------------|-------------|------|----------|---------------|-----------------|----------|---------------|-----------------|
| 0        | 1             | 3           | ccc  | 0        | 0             | 0               | 0        | 0             | 0               |

### Usage
```bash
php bin/compressor.php naive compress 256KB data/alice.txt data/alice.txt.compressed

php bin/compressor.php naive decompress 256KB data/alice.txt.compressed data/alice.txt.decompressed 
```

### Info
| Buffer size | Compress time | Decompress time | Original file size | Compressed file size | Compressed file size diff | Hash match |
|-------------|---------------|-----------------|--------------------|----------------------|---------------------------|------------|
| 2KB         | 0.088s        | 0.063s          | 152175             | 153885               | -1.12%                    | yes        |
| 4KB         | 0.108s        | 0.075s          | 152175             | 153890               | -1.13%                    | yes        |
| 8KB         | 0.128s        | 0.086s          | 152175             | 153519               | -0.88%                    | yes        |
| 16KB        | 0.156s        | 0.107s          | 152175             | 152570               | -0.26%                    | yes        |
| 32KB        | 0.186s        | 0.117s          | 152175             | 151089               | 0.71%                     | yes        |
| 64KB        | 0.218s        | 0.137s          | 152175             | 149130               | 2.00%                     | yes        |
| 128KB       | 0.266s        | 0.132s          | 152175             | 147054               | 3.37%                     | yes        |
| 256KB       | 0.294s        | 0.143s          | 152175             | 145846               | 4.16%                     | yes        |

## RLE

https://en.wikipedia.org/wiki/Run-length_encoding

### Structure

| text length | text  | duplicate chars count | duplicate char |
|-------------|-------|-----------------------|----------------|
| 8 bit       | N bit | 8 bit                 | 8 bit          |

### Example

`abcdeee`

| text length | text | duplicate chars count | duplicate char |
|-------------|------|-----------------------|----------------|
| 4           | abcd | -3                    | e              |



```bash
php bin/compressor.php rle compress 2KB data/alice.txt data/alice.txt.compressed
php bin/compressor.php rle decompress 2KB data/alice.txt.compressed data/alice.txt.decompressed 
```

| Compress time | Decompress time | Original file size | Compressed file size | Compressed file size diff | Hash match |
|---------------|-----------------|--------------------|----------------------|---------------------------|------------|
| 0.567s        | 0.098s          | 152175             | 154382               | -1.45%                    | yes        |

## LZ77

https://ru.wikipedia.org/wiki/LZ77

### Structure

| offset | length | char  |
|--------|--------|-------|
| 8 bit  | 8 bit  | 8 bit |

### Example

`ababd`

| offset | length | char | offset | length | char | offset | length | char |
|--------|--------|------|--------|--------|------|--------|--------|------|
| 0      | 0      | a    | 0      | 0      | b    | 2      | 2      | d    |


```bash
php bin/compressor.php lz77 compress 64KB data/alice.txt data/alice.txt.compressed
php bin/compressor.php lz77 decompress 64KB data/alice.txt.compressed data/alice.txt.decompressed 
```

| Buffer size | Compress time | Decompress time | Original file size | Compressed file size | Compressed file size diff | Hash match |
|-------------|---------------|-----------------|--------------------|----------------------|---------------------------|------------|
| 2KB         | 0.488s        | 0.192s          | 152175             | 125199               | 17.73%                    | yes        |
| 4KB         | 0.508s        | 0.170s          | 152175             | 112531               | 26.05%                    | yes        |
| 8KB         | 0.597s        | 0.181s          | 152175             | 102047               | 32.94%                    | yes        |
| 16KB        | 0.784s        | 0.171s          | 152175             | 93195                | 38.76%                    | yes        |
| 32KB        | 1.109s        | 0.160s          | 152175             | 85799                | 43.62%                    | yes        |
| 64KB        | 1.583s        | 0.165s          | 152175             | 80763                | 46.93%                    | yes        |

## LZ77M

### Structure

| text/char bit | text length | text  | text/char bit | offset | length | char  |
|---------------|-------------|-------|---------------|--------|--------|-------|
| 1 bit         | 7 bit       | N bit | 1 bit         | 7 bit  | 8 bit  | 8 bit |

### Example

`ababd`

| text/char bit | text length | text | text/char bit | offset | length | char |
|---------------|-------------|------|---------------|--------|--------|------|
| 1             | 2           | ab   | 0             | 2      | 2      | d    |

```bash
php bin/compressor.php lz77m compress 32KB data/alice.txt data/alice.txt.compressed
php bin/compressor.php lz77m decompress 32KB data/alice.txt.compressed data/alice.txt.decompressed 
```

| Buffer size | Compress time | Decompress time | Original file size | Compressed file size | Compressed file size diff | Hash match |
|-------------|---------------|-----------------|--------------------|----------------------|---------------------------|------------|
| 2KB         | 0.475s        | 0.186s          | 152175             | 124948               | 17.89%                    | yes        |
| 4KB         | 0.511s        | 0.173s          | 152175             | 112410               | 26.13%                    | yes        |
| 8KB         | 0.610s        | 0.163s          | 152175             | 101956               | 33.00%                    | yes        |
| 16KB        | 0.794s        | 0.168s          | 152175             | 93124                | 38.80%                    | yes        |
| 32KB        | 1.110s        | 0.167s          | 152175             | 85731                | 43.66%                    | yes        |

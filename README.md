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
| 2KB         | 0.135s        | 0.067s          | 152,175B           | 153,829B             | -1.09%                    | yes        |
| 4KB         | 0.094s        | 0.070s          | 152,175B           | 153,849B             | -1.10%                    | yes        |
| 8KB         | 0.124s        | 0.093s          | 152,175B           | 153,399B             | -0.80%                    | yes        |
| 16KB        | 0.164s        | 0.108s          | 152,175B           | 152,546B             | -0.24%                    | yes        |
| 32KB        | 0.193s        | 0.113s          | 152,175B           | 151,119B             | 0.69%                     | yes        |
| 64KB        | 0.252s        | 0.157s          | 152,175B           | 149,177B             | 1.97%                     | yes        |
| 128KB       | 0.276s        | 0.132s          | 152,175B           | 147,155B             | 3.30%                     | yes        |
| 256KB       | 0.289s        | 0.142s          | 152,175B           | 145,951B             | 4.09%                     | yes        |

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
| 0.642s        | 0.097s          | 152,175B           | 154,382B             | -1.45%                    | yes        |

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
| 2KB         | 0.484s        | 0.183s          | 152,175B           | 125,199B             | 17.73%                    | yes        |
| 4KB         | 0.511s        | 0.166s          | 152,175B           | 112,531B             | 26.05%                    | yes        |
| 8KB         | 0.613s        | 0.159s          | 152,175B           | 102,047B             | 32.94%                    | yes        |
| 16KB        | 0.775s        | 0.162s          | 152,175B           | 93,195B              | 38.76%                    | yes        |
| 32KB        | 1.079s        | 0.163s          | 152,175B           | 85,799B              | 43.62%                    | yes        |
| 64KB        | 1.564s        | 0.250s          | 152,175B           | 80,763B              | 46.93%                    | yes        |

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
| 2KB         | 0.470s        | 0.176s          | 152,175B           | 124,948B             | 17.89%                    | yes        |
| 4KB         | 0.498s        | 0.174s          | 152,175B           | 112,410B             | 26.13%                    | yes        |
| 8KB         | 0.604s        | 0.160s          | 152,175B           | 101,956B             | 33.00%                    | yes        |
| 16KB        | 0.778s        | 0.162s          | 152,175B           | 93,124B              | 38.80%                    | yes        |
| 32KB        | 1.085s        | 0.164s          | 152,175B           | 85,731B              | 43.66%                    | yes        |

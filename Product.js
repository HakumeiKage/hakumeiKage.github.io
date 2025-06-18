const products = [
    {
        id: 1,
        title: "Renewed - HP EliteBook 840 G2 Business Laptop",
        price: "$200",
        category: "laptops",
        description: "14-inch Display, 1600 x 900 Resolution, Intel Core i5-5300U 5th Generation CPU, DDR3L RAM, SSD, Windows 10 Pro ",
        colors: [
            {
                name: "Grey",
                mainImage: "images/product1_grey_1.webp",
                thumbnails: [
                    "images/product1_grey_2.webp",
                    "images/product1_grey_3.jpg",
                    "images/product1_grey_4.webp"
                ],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            },
            {
                name: "Blue",
                mainImage: "images/product1_blue_1.jpg",
                thumbnails: [
                    "images/product1_blue_2.jpg",
                    "images/product1_blue_3.jpg",
                    "images/product1_blue_4.jpg"
                ],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            },
            {
                name: "Red",
                mainImage: "images/product1_red_1.avif",
                thumbnails: [
                    "images/product1_red_2.avif",
                    "images/product1_red_3.avif",
                    "images/product1_red_4.avif"
                ],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            },
            {
                name: "White",
                mainImage: "images/product1_white_1.jpg",
                thumbnails: [
                    "images/product1_white_2.jpg.webp",
                    "images/product1_white_3.jpg",
                    "images/product1_white_4.jpg"
                ],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            },
            {
                name: "Gold",
                mainImage: "images/product1_gold_1.avif",
                thumbnails: [
                    "images/product1_gold_2.avif",
                    "images/product1_gold_3.avif",
                    "images/product1_gold_4.avif"
                ],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            }
        ]
    },
    {
        id: 2,
        title: "Apple MacBook Air",
        price: "$500",
        category: "laptops",
        description: "13-inch, Apple M1 chip with 8‑core CPU and 7‑core GPU, Unified Memory",
        colors: [
            {
                name: "Space Gray",
                mainImage: "images/product2_apple_1.jpg",
                thumbnails: [
                    "images/product2_apple_2.jpg",
                    "images/product2_apple_3.jpg",
                    "images/product2_apple_4.jpg"
                ],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            }
        ]
    },
    {
        id: 3,
        title: "Dell OptiPlex 7050 Intel i5",
        price: "$100",
        category: "desktops",
        description: "Intel Core i5-7500 - 7th Gen Quad-Core 3.40GHz 6M Cache, Max Turbo Frequency up to 3.80GHz Intel Turbo Boost 2.0 Technology",
        colors: [
            {
                name: "Black",
                mainImage: "images/product3_black_1.webp",
                thumbnails: [],
                sizes: ["S", "M", "L", "XL", "XXL"]
            }
        ]
    },
    {
        id: 4,
        title: "Thermaltake Mid Tower Computer Case",
        price: "$200",
        category: "components",
        description: "Thermaltake Core P6 TG CA-1V2-00MBWN-00 Turquoise SPCC ATX Mid Tower Computer Case",
        colors: [
            {
                name: "Blue",
                mainImage: "images/product4_Blue_1.jpg",
                thumbnails: [],
                sizes: ["S", "M", "L", "XL", "XXL"]
            }
        ]
    },
    {
        id: 5,
        title: "Infinity XQ6HX ",
        price: "$600",
        category: "desktops",
        description: "Infinity XQ6HX-13R6A-899 Gaming Notebook - Display: 16.1 WQXGA 240Hz - CPU: Intel i7-13650HX - 4800MHz - PCIe 4.0 SSD - GPU: NVIDIA GeForce RTX 4060P 8GB GDDR6 - OS: Windows 11 Home - Network: Intel Wi-Fi 6 + Bluetooth 5.2 - I/O: GB LAN Port, HDMI 2.1, USB-C (3.2 Gen2), 2x USB-A (3.2 Gen1), Full-Size SD Card Reader, Microphone input, MIC+Headset, 1x USB-A 2.0 - HD Webcam",
        colors: [
            {
                name: "Light Blue",
                mainImage: "images/Product5_lightBlue_1.jpg",
                thumbnails: [],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            }
        ]
    },
    {
        id: 6,
        title: "HP 2022 All-in-One Desktop",
        price: "$600",
        category: "desktops",
        description: "HP 2022 All-in-One Desktop, 21.5 FHD Display, Intel Celeron J4025 Processor,PCIe SSD, Webcam, HDMI, RJ-45, Wired Keyboard&Mouse, WiFi, Windows 11 Home, White",
        colors: [
            {
                name: "transparent",
                mainImage: "images/product6_transparent_1.jpg",
                thumbnails: [],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            }
        ]
    },
    {
        id: 7,
        title: "GMKtec Mini PC",
        price: "$500",
        category: "desktops",
        description: "Intel Twin Lake N150 (Upgraded N100), DDR4 RAM PCIe M.2 SSD, Desktop Computer 4K Dual HDMI/USB3.2/WiFi 6/BT5.2/2.5GbE RJ45 for Office, Business.",
        colors: [
            {
                name: "Green",
                mainImage: "images/product7_green_1.webp",
                thumbnails: [],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            }
        ]
    },
    {
        id: 8,
        title: "KAMRUI Mini PC",
        price: "$500",
        category: "desktops",
        description: "Intel Processor N97 (up to 3.6 GHz), DDR4 RAM M.2 SSD, Mini Desktop Computer Support Dual 4K, WiFi, Bluetooth, Ethernet, HTPC for Business, Education, Home",
        colors: [
            {
                name: "silver",
                mainImage: "images/product8_silver_1.jpg",
                thumbnails: [],
                sizes: ["8GB x 64GB", "16GB x 128GB", "16GB x 256GB", "16GB x 512GB", "16GB x 1TB"]
            }
        ]
    }
];
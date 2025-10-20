# PS-Product-Site&PS-Product-Site-Importer


> WordPress 插件：用 **“产品目录 + 详情”** 方案快速搭建产品展示站。适配 Avada 等主题；含后台字段、REST 输出、短代码渲染；可选 CSV 批量导入器。

- 主插件下载（示例版本 v1.3.2）：`ps-product-site-v1.3.2.zip`
- CSV 导入器（可选）：`ps-product-site-importer.zip`
- 示例 CSV：`ps-products.csv`

---

## ✨ 功能概览
- 自定义文章类型 **产品**（CPT：`ps_product`）与 **产品分类**（TAX：`ps_category`）
- 后台元字段面板（型号、图库、亮点、场景、参数表、补充，见下方映射）
- REST API：`/wp-json/ps/v1/products`
- 短代码渲染 **产品目录 + 右侧详情**（默认 iframe 隔离，规避主题 CSS/JS 冲突）
- 一键全宽显示与版心宽度可配
- 可选 **CSV 批量导入**（按标题去重/更新）

---

## 🧩 字段映射（后台 → 前端/导入列）

| 后台元字段 | 用途/前端键 |
|---|---|
| `ps_sub` | 型号 / `sub` |
| `ps_img1` | 主图 / `img1`（留空则回退特色图像） |
| `ps_img2` | 图库2 / `A5` |
| `ps_img3` | 图库3 / `A8` |
| `ps_img4` | 图库4 / `A11` |
| `ps_features_title` | 亮点标题 / `A6` |
| `ps_features_lines` | 亮点条目（一行一条）/ `A7` |
| `ps_scenarios_title` | 场景标题 / `A9` |
| `ps_scenarios_lines` | 场景条目（一行一条）/ `A10` |
| `ps_table1` | 参数表1（HTML `<table>`）/ `table1` |
| `ps_table2` | 参数表2（HTML `<table>`）/ `table2` |
| `ps_extra_text` | 补充1 / `A12` |
| `ps_extra2` | 补充2 / `Unnamed:_17` |
| `ps_extra3` | 补充3 / `Unnamed:_18` |

> 文章正文即 `desc`；分类对应 `内容栏目`。

---

## 🔌 REST 接口
- **URL**：`/wp-json/ps/v1/products`
- **返回**：数组，每项含 `title, sub, desc, 内容栏目, img1, A5, A8, A11, A6, A7, A9, A10, A12, Unnamed:_17, Unnamed:_18, table1, table2`。

---

## 🧱 短代码用法

基础用法（推荐全宽）：
```text
[product_catalog fullwidth="1" maxwidth="1280"]
